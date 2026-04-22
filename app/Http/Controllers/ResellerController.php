<?php

namespace App\Http\Controllers;

use App\Models\CatalogService;
use App\Models\Order;
use App\Models\TopupRequest;
use App\Models\WalletTransaction;
use App\Services\OsemApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ResellerController extends Controller
{
    public function __construct(private readonly OsemApiService $osemApiService)
    {
    }

    public function catalog(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:80'],
            'search' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'in:malaysian,refill,non_refill'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ]);

        $query = CatalogService::query()
            ->where('is_active', true)
            ->orderBy('sell_rate')
            ->orderBy('name');

        if (! empty($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        if (! empty($validated['platform'])) {
            $platform = $validated['platform'];
            $query->where(function ($builder) use ($platform): void {
                $builder
                    ->where('category', 'like', "%{$platform}%")
                    ->orWhere('name', 'like', "%{$platform}%");
            });
        }

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('external_service_id', 'like', "%{$search}%");
            });
        }

        if (! empty($validated['tags'])) {
            $searchableColumns = "LOWER(CONCAT_WS(' ', name, category, COALESCE(meta, '')))";

            foreach ($validated['tags'] as $tag) {
                if ($tag === 'malaysian') {
                    $query->where(function ($builder) use ($searchableColumns): void {
                        $builder
                            ->whereRaw("{$searchableColumns} like ?", ['%malaysia%'])
                            ->orWhereRaw("{$searchableColumns} like ?", ['%malaysian%']);
                    });
                }

                if ($tag === 'refill') {
                    $query->where(function ($builder) use ($searchableColumns): void {
                        $builder
                            ->whereRaw("{$searchableColumns} like ?", ['%refill%'])
                            ->whereRaw("{$searchableColumns} not like ?", ['%non refill%'])
                            ->whereRaw("{$searchableColumns} not like ?", ['%no refill%'])
                            ->whereRaw("{$searchableColumns} not like ?", ['%non-refill%'])
                            ->whereRaw("{$searchableColumns} not like ?", ['%nr %']);
                    });
                }

                if ($tag === 'non_refill') {
                    $query->where(function ($builder) use ($searchableColumns): void {
                        $builder
                            ->whereRaw("{$searchableColumns} like ?", ['%non refill%'])
                            ->orWhereRaw("{$searchableColumns} like ?", ['%non-refill%'])
                            ->orWhereRaw("{$searchableColumns} like ?", ['%no refill%'])
                            ->orWhereRaw("{$searchableColumns} like ?", ['% nr %'])
                            ->orWhereRaw("{$searchableColumns} like ?", ['%[nr]%']);
                    });
                }
            }
        }

        $perPage = $validated['per_page'] ?? 50;

        $paginator = $query
            ->select(['id', 'external_service_id', 'name', 'category', 'type', 'min', 'max', 'sell_rate', 'meta'])
            ->paginate($perPage);

        $paginator->getCollection()->transform(function (CatalogService $service): array {
            return [
                'id' => $service->id,
                'external_service_id' => $service->external_service_id,
                'name' => $service->name,
                'category' => $service->category,
                'type' => $service->type,
                'min' => $service->min,
                'max' => $service->max,
                'sell_rate' => $service->sell_rate,
                'description' => $this->extractDescription($service->meta),
            ];
        });

        return response()->json($paginator);
    }

    public function catalogMeta(): JsonResponse
    {
        $categories = CatalogService::query()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        return response()->json([
            'total_services' => CatalogService::query()->where('is_active', true)->count(),
            'categories' => $categories,
        ]);
    }

    public function wallet(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'balance' => (float) $user->wallet_balance,
        ]);
    }

    public function topupRequests(Request $request): JsonResponse
    {
        $requests = TopupRequest::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(20)
            ->get(['id', 'amount', 'payment_method', 'status', 'reference', 'notes', 'admin_notes', 'created_at']);

        return response()->json([
            'data' => $requests,
        ]);
    }

    public function createTopupRequest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10', 'max:50000'],
            'payment_method' => ['required', 'string', 'in:bank-transfer,duitnow,tng,manual'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $topupRequest = TopupRequest::query()->create([
            'user_id' => $request->user()->id,
            'amount' => round((float) $validated['amount'], 4),
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Topup request submitted. Waiting for approval.',
            'data' => $topupRequest,
        ], 201);
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'catalog_service_id' => ['required', 'integer', 'exists:catalog_services,id'],
            'link' => ['required', 'string', 'max:2048'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $catalogService = CatalogService::query()->where('is_active', true)->find($validated['catalog_service_id']);

        if ($catalogService === null) {
            return response()->json(['message' => 'Service not available.'], 422);
        }

        if ($validated['quantity'] < $catalogService->min || $validated['quantity'] > $catalogService->max) {
            return response()->json([
                'message' => sprintf('Quantity must be between %d and %d.', $catalogService->min, $catalogService->max),
            ], 422);
        }

        $units = $validated['quantity'] / 1000;
        $customerPrice = round($units * (float) $catalogService->sell_rate, 4);
        $providerCost = round($units * (float) $catalogService->raw_rate, 4);
        $profit = round($customerPrice - $providerCost, 4);

        try {
            $result = DB::transaction(function () use ($request, $validated, $catalogService, $customerPrice, $providerCost, $profit) {
                $user = $request->user()
                    ->newQuery()
                    ->whereKey($request->user()->id)
                    ->lockForUpdate()
                    ->first();

                if ($user === null || (float) $user->wallet_balance < $customerPrice) {
                    throw new \RuntimeException('Insufficient wallet balance.');
                }

                $before = (float) $user->wallet_balance;
                $after = round($before - $customerPrice, 4);

                $user->wallet_balance = $after;
                $user->save();

                $providerResponse = $this->osemApiService->addOrder([
                    'service' => $catalogService->external_service_id,
                    'link' => $validated['link'],
                    'quantity' => $validated['quantity'],
                ]);

                $externalOrderId = isset($providerResponse['order']) ? (int) $providerResponse['order'] : null;

                if ($externalOrderId === null || $externalOrderId < 1) {
                    throw new \RuntimeException($providerResponse['error'] ?? 'Provider rejected order.');
                }

                $order = Order::query()->create([
                    'user_id' => $user->id,
                    'external_order_id' => $externalOrderId,
                    'service_id' => $catalogService->external_service_id,
                    'link' => $validated['link'],
                    'quantity' => $validated['quantity'],
                    'status' => 'Pending',
                    'charge' => (string) $providerCost,
                    'provider_cost' => $providerCost,
                    'customer_price' => $customerPrice,
                    'profit' => $profit,
                    'order_channel' => 'reseller',
                    'last_response' => $providerResponse,
                ]);

                WalletTransaction::query()->create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'type' => 'debit',
                    'amount' => $customerPrice,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'description' => sprintf('Order payment for #%d', $externalOrderId),
                    'meta' => [
                        'catalog_service_id' => $catalogService->id,
                        'external_service_id' => $catalogService->external_service_id,
                    ],
                ]);

                return [$order, $after];
            });

            return response()->json([
                'message' => 'Order placed successfully.',
                'order' => $result[0],
                'wallet_balance' => $result[1],
            ], 201);
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Unable to reach provider API.'], 502);
        } catch (Throwable $exception) {
            Log::warning('Reseller order failed.', [
                'user_id' => $request->user()?->id,
                'catalog_service_id' => $validated['catalog_service_id'],
                'message' => $exception->getMessage(),
            ]);

            $status = $exception->getMessage() === 'Insufficient wallet balance.' ? 422 : 500;

            return response()->json(['message' => $exception->getMessage()], $status);
        }
    }

    public function myOrders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:80'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $query = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('order_channel', 'reseller')
            ->latest();

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return response()->json($query->paginate($validated['per_page'] ?? 10));
    }

    public function profitReport(Request $request): JsonResponse
    {
        $summary = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('order_channel', 'reseller')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(customer_price), 0) as total_sales')
            ->selectRaw('COALESCE(SUM(provider_cost), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(profit), 0) as total_profit')
            ->first();

        return response()->json([
            'orders_count' => (int) ($summary?->orders_count ?? 0),
            'total_sales' => (float) ($summary?->total_sales ?? 0),
            'total_cost' => (float) ($summary?->total_cost ?? 0),
            'total_profit' => (float) ($summary?->total_profit ?? 0),
        ]);
    }

    private function extractDescription(mixed $meta): ?string
    {
        if (! is_array($meta)) {
            return null;
        }

        $candidates = [
            $meta['description'] ?? null,
            $meta['desc'] ?? null,
            $meta['details'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $clean = trim(html_entity_decode(strip_tags($candidate)));

            if ($clean !== '') {
                return $clean;
            }
        }

        return null;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OsemApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class OsemController extends Controller
{
    public function __construct(private readonly OsemApiService $osemApiService)
    {
    }

    public function services(): JsonResponse
    {
        try {
            return response()->json($this->osemApiService->services());
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Unable to reach Osem API.'], 502);
        } catch (Throwable $exception) {
            Log::error('Failed to load Osem services.', ['message' => $exception->getMessage()]);

            return response()->json(['message' => 'Unable to load services.'], 500);
        }
    }

    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service' => ['required', 'integer', 'min:1'],
            'link' => ['nullable', 'string', 'max:2048'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'runs' => ['nullable', 'integer', 'min:1'],
            'interval' => ['nullable', 'integer', 'min:1'],
            'comments' => ['nullable', 'string', 'max:10000'],
            'usernames' => ['nullable', 'string', 'max:10000'],
            'username' => ['nullable', 'string', 'max:255'],
            'min' => ['nullable', 'integer', 'min:1'],
            'max' => ['nullable', 'integer', 'min:1'],
            'posts' => ['nullable', 'integer', 'min:0'],
            'old_posts' => ['nullable', 'integer', 'min:0'],
            'delay' => ['nullable', 'integer', 'min:0'],
            'expiry' => ['nullable', 'string', 'max:40'],
            'answer_number' => ['nullable', 'string', 'max:20'],
            'groups' => ['nullable', 'string', 'max:10000'],
        ]);

        if (! isset($validated['quantity']) && ! isset($validated['comments']) && ! isset($validated['usernames']) && ! isset($validated['username'])) {
            return response()->json([
                'message' => 'Please provide quantity or one of comments/usernames/username fields based on service type.',
            ], 422);
        }

        try {
            $response = $this->osemApiService->addOrder($validated);
            $externalOrderId = isset($response['order']) ? (int) $response['order'] : null;

            if ($externalOrderId === null || $externalOrderId < 1) {
                Log::warning('Osem add order did not return a valid order id.', ['response' => $response]);

                return response()->json([
                    'message' => $response['error'] ?? 'Order submission failed.',
                    'response' => $response,
                ], 422);
            }

            $order = Order::query()->create([
                'user_id' => $request->user()->id,
                'external_order_id' => $externalOrderId,
                'service_id' => $validated['service'],
                'link' => $validated['link'],
                'quantity' => $validated['quantity'],
                'runs' => $validated['runs'] ?? null,
                'interval' => $validated['interval'] ?? null,
                'status' => 'Pending',
                'charge' => $response['charge'] ?? null,
                'last_response' => $response,
            ]);

            Log::info('Osem order created.', [
                'order_id' => $order->id,
                'external_order_id' => $order->external_order_id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Order created successfully.',
                'order' => $order,
                'response' => $response,
            ], 201);
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Unable to reach Osem API.'], 502);
        } catch (Throwable $exception) {
            Log::error('Failed to add Osem order.', [
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Unable to submit order.'], 500);
        }
    }

    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $response = $this->osemApiService->status($validated['order']);
            $this->updateOrderFromStatusResponse($request->user()->id, $validated['order'], $response);

            return response()->json($response);
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Unable to reach Osem API.'], 502);
        } catch (Throwable $exception) {
            Log::error('Failed to check Osem order status.', [
                'order' => $validated['order'],
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Unable to fetch order status.'], 500);
        }
    }

    public function statuses(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orders' => ['required', 'array', 'min:1', 'max:50'],
            'orders.*' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $response = $this->osemApiService->multipleStatus($validated['orders']);

            foreach ($validated['orders'] as $externalOrderId) {
                $statusPayload = $response[(string) $externalOrderId] ?? null;

                if (is_array($statusPayload)) {
                    $this->updateOrderFromStatusResponse($request->user()->id, (int) $externalOrderId, $statusPayload);
                }
            }

            return response()->json($response);
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Unable to reach Osem API.'], 502);
        } catch (Throwable $exception) {
            Log::error('Failed to fetch multiple Osem statuses.', [
                'orders' => $validated['orders'],
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Unable to fetch multiple statuses.'], 500);
        }
    }

    public function refill(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $response = $this->osemApiService->refill($validated['order']);

            Order::query()
                ->where('user_id', $request->user()->id)
                ->where('external_order_id', $validated['order'])
                ->update([
                    'refill_status' => $response['refill'] ?? ($response['status'] ?? 'requested'),
                    'refill_requested_at' => now(),
                    'last_response' => $response,
                    'last_error' => $response['error'] ?? null,
                ]);

            return response()->json($response);
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Unable to reach Osem API.'], 502);
        } catch (Throwable $exception) {
            Log::error('Failed to request refill for Osem order.', [
                'order' => $validated['order'],
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Unable to request refill.'], 500);
        }
    }

    public function refills(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orders' => ['required', 'array', 'min:1', 'max:50'],
            'orders.*' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $response = $this->osemApiService->multipleRefill($validated['orders']);
            $providerError = $this->extractRefillError($response);

            foreach ($validated['orders'] as $externalOrderId) {
                Order::query()
                    ->where('user_id', $request->user()->id)
                    ->where('external_order_id', $externalOrderId)
                    ->update([
                        'refill_status' => $providerError ?? 'requested',
                        'refill_requested_at' => now(),
                        'last_response' => $response,
                        'last_error' => $providerError,
                    ]);
            }

            if ($providerError !== null) {
                return response()->json([
                    'message' => $providerError,
                    'response' => $response,
                ], 422);
            }

            return response()->json($response);
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Unable to reach Osem API.'], 502);
        } catch (Throwable $exception) {
            Log::error('Failed to request multiple refill for Osem orders.', [
                'orders' => $validated['orders'],
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Unable to request refill.'], 500);
        }
    }

    public function refillStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'refill' => ['required', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($this->osemApiService->refillStatus($validated['refill']));
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Unable to reach Osem API.'], 502);
        } catch (Throwable $exception) {
            Log::error('Failed to fetch refill status.', [
                'refill' => $validated['refill'],
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Unable to fetch refill status.'], 500);
        }
    }

    public function refillStatuses(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'refills' => ['required', 'array', 'min:1', 'max:50'],
            'refills.*' => ['required', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($this->osemApiService->multipleRefillStatus($validated['refills']));
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Unable to reach Osem API.'], 502);
        } catch (Throwable $exception) {
            Log::error('Failed to fetch multiple refill statuses.', [
                'refills' => $validated['refills'],
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Unable to fetch refill statuses.'], 500);
        }
    }

    public function cancel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $response = $this->osemApiService->cancel($validated['order']);
            $providerError = $this->extractCancelError($response);

            Order::query()
                ->where('user_id', $request->user()->id)
                ->where('external_order_id', $validated['order'])
                ->update([
                    'cancel_status' => $response['cancel'] ?? ($response['status'] ?? 'requested'),
                    'cancel_requested_at' => now(),
                    'last_response' => $response,
                    'last_error' => $providerError,
                ]);

            if ($providerError !== null) {
                return response()->json([
                    'message' => $providerError,
                    'response' => $response,
                ], 422);
            }

            return response()->json($response);
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Unable to reach Osem API.'], 502);
        } catch (Throwable $exception) {
            Log::error('Failed to cancel Osem order.', [
                'order' => $validated['order'],
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Unable to cancel order.'], 500);
        }
    }

    public function cancels(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orders' => ['required', 'array', 'min:1', 'max:50'],
            'orders.*' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $response = $this->osemApiService->multipleCancel($validated['orders']);

            foreach ($validated['orders'] as $externalOrderId) {
                Order::query()
                    ->where('user_id', $request->user()->id)
                    ->where('external_order_id', $externalOrderId)
                    ->update([
                        'cancel_status' => 'requested',
                        'cancel_requested_at' => now(),
                        'last_response' => $response,
                    ]);
            }

            return response()->json($response);
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Unable to reach Osem API.'], 502);
        } catch (Throwable $exception) {
            Log::error('Failed to cancel multiple Osem orders.', [
                'orders' => $validated['orders'],
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Unable to cancel orders.'], 500);
        }
    }

    public function orders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:80'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $query = Order::query()
            ->where('user_id', $request->user()->id)
            ->latest();

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('link', 'like', "%{$search}%")
                    ->orWhere('external_order_id', 'like', "%{$search}%")
                    ->orWhere('service_id', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate($perPage));
    }

    public function balance(): JsonResponse
    {
        try {
            return response()->json($this->osemApiService->balance());
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Unable to reach Osem API.'], 502);
        } catch (Throwable $exception) {
            Log::error('Failed to load Osem balance.', ['message' => $exception->getMessage()]);

            return response()->json(['message' => 'Unable to load balance.'], 500);
        }
    }

    private function updateOrderFromStatusResponse(int $userId, int $externalOrderId, array $response): void
    {
        $order = Order::query()
            ->where('user_id', $userId)
            ->where('external_order_id', $externalOrderId)
            ->first();

        if ($order === null) {
            return;
        }

        $order->status = $response['status'] ?? $order->status;
        $order->charge = $response['charge'] ?? $order->charge;
        $order->start_count = $response['start_count'] ?? $order->start_count;
        $order->remains = $response['remains'] ?? $order->remains;
        $order->currency = $response['currency'] ?? $order->currency;
        $order->last_polled_at = now();
        $order->last_response = $response;
        $order->last_error = $response['error'] ?? null;
        $order->markTerminalIfCompleted();
        $order->save();
    }

    private function extractCancelError(array $response): ?string
    {
        if (! empty($response['error']) && is_string($response['error'])) {
            return $response['error'];
        }

        $first = $response[0] ?? null;

        if (! is_array($first)) {
            return null;
        }

        $cancel = $first['cancel'] ?? null;

        if (is_array($cancel) && ! empty($cancel['error']) && is_string($cancel['error'])) {
            return $cancel['error'];
        }

        return null;
    }

    private function extractRefillError(array $response): ?string
    {
        if (! empty($response['error']) && is_string($response['error'])) {
            return $response['error'];
        }

        $first = $response[0] ?? null;

        if (! is_array($first)) {
            return null;
        }

        $refill = $first['refill'] ?? null;

        if (is_array($refill) && ! empty($refill['error']) && is_string($refill['error'])) {
            return $refill['error'];
        }

        return null;
    }
}

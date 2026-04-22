<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OsemApiService
{
    /**
     * @throws RuntimeException
     * @throws ConnectionException
     */
    private function send(array $payload): array
    {
        $apiKey = config('services.osem.api_key');
        $baseUrl = config('services.osem.base_url');

        if (empty($apiKey)) {
            throw new RuntimeException('OSEM_API_KEY is not configured.');
        }

        $response = Http::asForm()
            ->timeout(20)
            ->post($baseUrl, array_merge(['key' => $apiKey], $payload));

        if (! $response->successful()) {
            throw new RuntimeException('Failed to connect to Osem API.');
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Unexpected API response format.');
        }

        return $data;
    }

    public function services(): array
    {
        return $this->send(['action' => 'services']);
    }

    public function addOrder(array $payload): array
    {
        return $this->send(array_merge(['action' => 'add'], $payload));
    }

    public function status(int $order): array
    {
        return $this->send([
            'action' => 'status',
            'order' => $order,
        ]);
    }

    public function multipleStatus(array $orders): array
    {
        return $this->send([
            'action' => 'status',
            'orders' => implode(',', $orders),
        ]);
    }

    public function refill(int $order): array
    {
        return $this->send([
            'action' => 'refill',
            'order' => $order,
        ]);
    }

    public function multipleRefill(array $orders): array
    {
        return $this->send([
            'action' => 'refill',
            'orders' => implode(',', $orders),
        ]);
    }

    public function refillStatus(int $refillId): array
    {
        return $this->send([
            'action' => 'refill_status',
            'refill' => $refillId,
        ]);
    }

    public function multipleRefillStatus(array $refillIds): array
    {
        return $this->send([
            'action' => 'refill_status',
            'refills' => implode(',', $refillIds),
        ]);
    }

    public function cancel(int $order): array
    {
        $response = $this->send([
            'action' => 'cancel',
            'order' => $order,
        ]);

        if (! isset($response['error']) || ! str_contains(strtolower((string) $response['error']), 'incorrect request')) {
            return $response;
        }

        // Some providers only support orders=1,2,3 format for cancel.
        return $this->send([
            'action' => 'cancel',
            'orders' => (string) $order,
        ]);
    }

    public function multipleCancel(array $orders): array
    {
        return $this->send([
            'action' => 'cancel',
            'orders' => implode(',', $orders),
        ]);
    }

    public function balance(): array
    {
        return $this->send(['action' => 'balance']);
    }
}

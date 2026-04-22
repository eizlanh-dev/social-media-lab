<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OsemApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class PollOsemOrderStatusJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $orderId)
    {
    }

    public function handle(OsemApiService $osemApiService): void
    {
        $order = Order::query()->find($this->orderId);

        if ($order === null || $order->external_order_id === null) {
            return;
        }

        try {
            $response = $osemApiService->status((int) $order->external_order_id);

            $order->status = $response['status'] ?? $order->status;
            $order->charge = $response['charge'] ?? $order->charge;
            $order->start_count = $response['start_count'] ?? $order->start_count;
            $order->remains = $response['remains'] ?? $order->remains;
            $order->currency = $response['currency'] ?? $order->currency;
            $order->last_response = $response;
            $order->last_error = $response['error'] ?? null;
            $order->last_polled_at = now();
            $order->markTerminalIfCompleted();
            $order->save();
        } catch (Throwable $exception) {
            Log::error('Failed to poll Osem order status.', [
                'order_id' => $order->id,
                'external_order_id' => $order->external_order_id,
                'message' => $exception->getMessage(),
            ]);

            $order->last_polled_at = now();
            $order->last_error = $exception->getMessage();
            $order->save();

            throw $exception;
        }
    }
}

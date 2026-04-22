<?php

namespace App\Console\Commands;

use App\Jobs\PollOsemOrderStatusJob;
use App\Models\Order;
use Illuminate\Console\Command;

class PollOsemOrdersCommand extends Command
{
    protected $signature = 'osem:poll-orders {--limit=100 : Max number of orders to poll per run}';

    protected $description = 'Dispatch queue jobs to poll Osem order statuses.';

    public function handle(): int
    {
        $limit = max((int) $this->option('limit'), 1);
        $terminalStatuses = ['Completed', 'Partial', 'Canceled', 'Cancelled', 'Refunded', 'Failed'];

        $orders = Order::query()
            ->whereNotNull('external_order_id')
            ->where(function ($query) use ($terminalStatuses): void {
                $query
                    ->whereNull('status')
                    ->orWhereNotIn('status', $terminalStatuses);
            })
            ->oldest('last_polled_at')
            ->limit($limit)
            ->get(['id']);

        foreach ($orders as $order) {
            PollOsemOrderStatusJob::dispatch($order->id);
        }

        $this->info(sprintf('Dispatched %d order status polling jobs.', $orders->count()));

        return self::SUCCESS;
    }
}

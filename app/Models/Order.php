<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'external_order_id',
        'service_id',
        'link',
        'quantity',
        'runs',
        'interval',
        'status',
        'charge',
        'provider_cost',
        'customer_price',
        'profit',
        'order_channel',
        'start_count',
        'remains',
        'currency',
        'refill_status',
        'cancel_status',
        'last_error',
        'last_polled_at',
        'refill_requested_at',
        'cancel_requested_at',
        'completed_at',
        'last_response',
    ];

    protected function casts(): array
    {
        return [
            'last_response' => 'array',
            'provider_cost' => 'decimal:4',
            'customer_price' => 'decimal:4',
            'profit' => 'decimal:4',
            'last_polled_at' => 'datetime',
            'refill_requested_at' => 'datetime',
            'cancel_requested_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markTerminalIfCompleted(): void
    {
        if ($this->completed_at !== null) {
            return;
        }

        $terminalStatuses = ['completed', 'partial', 'canceled', 'cancelled', 'refunded', 'failed'];

        if (in_array(strtolower((string) $this->status), $terminalStatuses, true)) {
            $this->completed_at = now();
        }
    }
}

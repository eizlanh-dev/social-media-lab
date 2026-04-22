<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreditWalletCommand extends Command
{
    protected $signature = 'wallet:credit {user_id : User ID to credit} {amount : Amount to add}';

    protected $description = 'Credit balance to a user wallet for reseller ordering.';

    public function handle(): int
    {
        $userId = (int) $this->argument('user_id');
        $amount = round((float) $this->argument('amount'), 4);

        if ($amount <= 0) {
            $this->error('Amount must be greater than zero.');
            return self::FAILURE;
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            $this->error('User not found.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($user, $amount): void {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->first();

            if ($lockedUser === null) {
                throw new \RuntimeException('Unable to lock user row.');
            }

            $before = (float) $lockedUser->wallet_balance;
            $after = round($before + $amount, 4);

            $lockedUser->wallet_balance = $after;
            $lockedUser->save();

            WalletTransaction::query()->create([
                'user_id' => $lockedUser->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => 'Manual wallet credit via CLI',
            ]);
        });

        $this->info(sprintf('Wallet credited successfully. User #%d +%.4f', $user->id, $amount));

        return self::SUCCESS;
    }
}

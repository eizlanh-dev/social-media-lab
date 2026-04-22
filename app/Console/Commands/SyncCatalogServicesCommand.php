<?php

namespace App\Console\Commands;

use App\Models\CatalogService;
use App\Services\OsemApiService;
use Illuminate\Console\Command;

class SyncCatalogServicesCommand extends Command
{
    protected $signature = 'osem:sync-services
                            {--markup=20 : Percentage markup to set sell rate when creating new service}';

    protected $description = 'Sync provider services into local reseller catalog.';

    public function handle(OsemApiService $osemApiService): int
    {
        $markup = max((float) $this->option('markup'), 0);

        $services = $osemApiService->services();

        if (! is_array($services)) {
            $this->error('Unexpected provider response while syncing services.');

            return self::FAILURE;
        }

        $synced = 0;

        foreach ($services as $service) {
            if (! isset($service['service'])) {
                continue;
            }

            $externalServiceId = (int) $service['service'];
            $rawRate = round((float) ($service['rate'] ?? 0), 6);

            $catalog = CatalogService::query()->firstOrNew([
                'external_service_id' => $externalServiceId,
            ]);

            $catalog->name = (string) ($service['name'] ?? "Service {$externalServiceId}");
            $catalog->category = (string) ($service['category'] ?? 'General');
            $catalog->type = isset($service['type']) ? (string) $service['type'] : null;
            $catalog->min = max((int) ($service['min'] ?? 1), 1);
            $catalog->max = max((int) ($service['max'] ?? 1), 1);
            $catalog->raw_rate = $rawRate;
            $catalog->is_active = true;
            $catalog->meta = $service;

            if (! $catalog->exists || (float) $catalog->sell_rate <= 0) {
                $catalog->sell_rate = round($rawRate * (1 + ($markup / 100)), 6);
            }

            $catalog->save();
            $synced++;
        }

        $this->info(sprintf('Synced %d services into reseller catalog.', $synced));

        return self::SUCCESS;
    }
}

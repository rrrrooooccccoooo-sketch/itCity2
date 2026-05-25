<?php

namespace App\Console\Commands;

use App\Models\ComputerAsset;
use App\Models\Tenant;
use App\Support\ComputerAssetInventorySummary;
use Illuminate\Console\Command;

class BackfillComputerAssetInventorySummary extends Command
{
    protected $signature = 'inventory:backfill-computer-assets
        {--tenant=* : IDs de tenant a procesar}
        {--chunk=100 : Tamaño de lote para recorrer assets}
        {--refresh : Recalcula y sobrescribe columnas resumidas cuando exista inventario}';

    protected $description = 'Rellena columnas resumidas del inventario de equipos a partir de details.inventory en bases tenant.';

    private const SUMMARY_FIELDS = [
        'inventory_last_captured_at',
        'domain_name',
        'bios_version',
        'motherboard_product',
        'motherboard_serial',
        'antivirus_summary',
        'installed_programs_count',
        'hotfix_count',
        'primary_ip_address',
        'primary_mac_address',
        'operating_system_version',
        'operating_system_build',
        'primary_gpu',
        'memory_modules_count',
        'physical_disks_count',
    ];

    public function handle(): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $refresh = (bool) $this->option('refresh');
        $tenantIds = collect((array) $this->option('tenant'))
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->values();

        $tenants = Tenant::query()
            ->when($tenantIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $tenantIds->all()))
            ->orderBy('id')
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants para procesar.');

            return self::SUCCESS;
        }

        $tenantsProcessed = 0;
        $assetsScanned = 0;
        $assetsUpdated = 0;

        foreach ($tenants as $tenant) {
            $domain = optional($tenant->domains()->first())->domain ?: 'sin-dominio';
            $this->line(sprintf('Procesando tenant %s (%s)...', $tenant->id, $domain));

            $tenantScanned = 0;
            $tenantUpdated = 0;

            $tenant->run(function () use ($chunkSize, $refresh, &$tenantScanned, &$tenantUpdated): void {
                ComputerAsset::query()
                    ->orderBy('id')
                    ->chunkById($chunkSize, function ($assets) use ($refresh, &$tenantScanned, &$tenantUpdated): void {
                        foreach ($assets as $asset) {
                            $tenantScanned++;

                            $payload = $this->buildBackfillPayload($asset, $refresh);
                            if ($payload === []) {
                                continue;
                            }

                            $asset->fill($payload);

                            if (!$asset->isDirty()) {
                                continue;
                            }

                            $asset->save();
                            $tenantUpdated++;
                        }
                    });
            });

            $tenantsProcessed++;
            $assetsScanned += $tenantScanned;
            $assetsUpdated += $tenantUpdated;

            $this->info(sprintf('Tenant %s listo: %d assets revisados, %d actualizados.', $tenant->id, $tenantScanned, $tenantUpdated));
        }

        $this->newLine();
        $this->info(sprintf(
            'Backfill completado: %d tenants, %d assets revisados, %d assets actualizados.',
            $tenantsProcessed,
            $assetsScanned,
            $assetsUpdated
        ));

        return self::SUCCESS;
    }

    private function buildBackfillPayload(ComputerAsset $asset, bool $refresh): array
    {
        $details = $asset->details;
        if (!is_array($details)) {
            return [];
        }

        $summary = ComputerAssetInventorySummary::extract($details);
        $payload = [];

        foreach (self::SUMMARY_FIELDS as $field) {
            if (!array_key_exists($field, $summary)) {
                continue;
            }

            $incoming = $summary[$field];
            if (!$this->hasMeaningfulValue($incoming)) {
                continue;
            }

            $current = $asset->getAttribute($field);
            if (!$refresh && $this->hasMeaningfulValue($current)) {
                continue;
            }

            $payload[$field] = $field === 'primary_mac_address'
                ? $this->normalizeMacAddress(is_scalar($incoming) ? (string) $incoming : null)
                : $incoming;
        }

        return $payload;
    }

    private function hasMeaningfulValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }

    private function normalizeMacAddress(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return strtoupper(str_replace('-', ':', trim($value)));
    }
}
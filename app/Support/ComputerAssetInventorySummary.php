<?php

namespace App\Support;

class ComputerAssetInventorySummary
{
    public static function extract(array $details): array
    {
        $inventory = is_array(data_get($details, 'inventory')) ? data_get($details, 'inventory') : [];
        $hardware = is_array(data_get($inventory, 'hardware')) ? data_get($inventory, 'hardware') : [];
        $software = is_array(data_get($inventory, 'software')) ? data_get($inventory, 'software') : [];
        $antivirus = self::normalizeInventoryList(data_get($software, 'antivirus'));
        $installedPrograms = self::normalizeInventoryList(data_get($software, 'installed_programs'));
        $memoryModules = self::normalizeInventoryList(data_get($hardware, 'memory_modules'));
        $physicalDisks = self::normalizeInventoryList(data_get($hardware, 'physical_disks'));
        $videoControllers = self::normalizeInventoryList(data_get($hardware, 'video_controllers'));
        $networkAdapters = self::normalizeInventoryList(data_get($hardware, 'network_adapters'));

        $antivirusNames = collect($antivirus)
            ->map(fn (array $product) => trim((string) data_get($product, 'display_name', '')))
            ->filter()
            ->unique()
            ->values();

        $capturedAt = data_get($inventory, 'captured_at');

        return [
            'inventory_last_captured_at' => $capturedAt ?: null,
            'domain_name' => data_get($hardware, 'system.domain') ?: null,
            'bios_version' => self::stringifyValue(data_get($hardware, 'bios.version')),
            'motherboard_product' => data_get($hardware, 'motherboard.product') ?: null,
            'motherboard_serial' => data_get($hardware, 'motherboard.serial_number') ?: null,
            'antivirus_summary' => $antivirusNames->isNotEmpty() ? $antivirusNames->join(', ') : null,
            'installed_programs_count' => is_numeric(data_get($software, 'installed_programs_total'))
                ? (int) data_get($software, 'installed_programs_total')
                : (is_array(data_get($software, 'installed_programs')) ? count($installedPrograms) : null),
            'hotfix_count' => data_get($software, 'hotfix_count'),
            'primary_ip_address' => data_get($hardware, 'network.primary_ip_address')
                ?: self::firstNetworkValue($networkAdapters, 'ip_addresses'),
            'primary_mac_address' => data_get($hardware, 'network.primary_mac_address')
                ?: self::firstNetworkMacAddress($networkAdapters),
            'operating_system_version' => data_get($software, 'operating_system.version') ?: null,
            'operating_system_build' => data_get($software, 'operating_system.build_number') ?: null,
            'primary_gpu' => data_get($hardware, 'video.primary_gpu')
                ?: (data_get($videoControllers, '0.name') ?: null),
            'memory_modules_count' => is_array(data_get($hardware, 'memory_modules')) ? count($memoryModules) : null,
            'physical_disks_count' => is_array(data_get($hardware, 'physical_disks')) ? count($physicalDisks) : null,
        ];
    }

    private static function normalizeInventoryList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, fn ($item) => is_array($item)));
    }

    private static function stringifyValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $parts = collect($value)
                ->map(fn ($item) => is_scalar($item) ? trim((string) $item) : '')
                ->filter()
                ->unique()
                ->values();

            return $parts->isNotEmpty() ? $parts->join(' / ') : null;
        }

        if (is_scalar($value)) {
            $text = trim((string) $value);

            return $text !== '' ? $text : null;
        }

        return null;
    }

    private static function firstNetworkValue(array $networkAdapters, string $key): ?string
    {
        foreach ($networkAdapters as $adapter) {
            $values = data_get($adapter, $key);
            if (!is_array($values)) {
                continue;
            }

            foreach ($values as $value) {
                if (!is_scalar($value)) {
                    continue;
                }

                $text = trim((string) $value);
                if ($text !== '') {
                    return $text;
                }
            }
        }

        return null;
    }

    private static function firstNetworkMacAddress(array $networkAdapters): ?string
    {
        foreach ($networkAdapters as $adapter) {
            $value = data_get($adapter, 'mac_address');
            if (!is_scalar($value)) {
                continue;
            }

            $text = strtoupper(str_replace('-', ':', trim((string) $value)));
            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }
}
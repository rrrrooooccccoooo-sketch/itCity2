<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\ComputerAsset;

class UpdateImportedAssetsInvoiceFolio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:update-invoice-folio {folio : Invoice folio to set} {--date= : Date (YYYY-MM-DD) to target, defaults to today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update imported assets (spreadsheet imports) for a given date and set procurement.invoice_folio';

    public function handle(): int
    {
        $folio = (string) $this->argument('folio');
        $date = (string) ($this->option('date') ?: Carbon::today()->toDateString());

        if (!$this->confirm("Confirmar: establecer folio '{$folio}' en activos importados el {$date}?", true)) {
            $this->info('Cancelado.');
            return 0;
        }

        $this->info("Buscando activos importados con fecha de importación que empiece con: {$date}");

        $assets = ComputerAsset::query()
            ->where('details->import->source', 'spreadsheet_layout')
            ->where('details->import->imported_at', 'like', $date . '%')
            ->get();

        $count = $assets->count();
        if ($count === 0) {
            $this->info('No se encontraron activos para la fecha indicada.');
            return 0;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($assets as $asset) {
            $details = is_array($asset->details) ? $asset->details : (is_string($asset->details) && $asset->details !== '' ? json_decode($asset->details, true) : []);
            data_set($details, 'procurement.invoice_folio', $folio);
            $asset->details = $details;
            $asset->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Actualizados: {$count} activos. Folio establecido a '{$folio}'.");

        return 0;
    }
}

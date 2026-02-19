<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryMovement;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('inventory:backfill-balance {--inventory_id=} {--dry-run}', function () {
    $inventoryId = $this->option('inventory_id');
    $dryRun = (bool) $this->option('dry-run');

    $query = Inventory::query()->orderBy('id');

    if (!empty($inventoryId)) {
        $query->where('id', $inventoryId);
    }

    $totalUpdated = 0;

    $query->chunkById(50, function ($inventories) use ($dryRun, &$totalUpdated) {
        foreach ($inventories as $inventory) {
            $movements = InventoryMovement::query()
                ->where('inventory_id', $inventory->id)
                ->orderBy('id')
                ->get(['id', 'type', 'qty']);

            if ($movements->isEmpty()) {
                $this->info("Inventory {$inventory->id}: no movements");
                continue;
            }

            $netDelta = $movements->reduce(function ($carry, $movement) {
                if ($movement->type === 'in') {
                    return $carry + (float) $movement->qty;
                }

                if ($movement->type === 'out') {
                    return $carry - (float) $movement->qty;
                }

                return $carry;
            }, 0.0);

            $running = (float) $inventory->stock_on_hand - $netDelta;

            foreach ($movements as $movement) {
                $stockBefore = $running;

                if ($movement->type === 'in') {
                    $running += (float) $movement->qty;
                } elseif ($movement->type === 'out') {
                    $running -= (float) $movement->qty;
                }

                $stockAfter = $running;

                if (!$dryRun) {
                    InventoryMovement::whereKey($movement->id)->update([
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                    ]);
                }
            }

            $totalUpdated += $movements->count();
            $this->info("Inventory {$inventory->id}: " . $movements->count() . " movements updated");
        }
    });

    $this->info($dryRun
        ? "Dry run complete. {$totalUpdated} movements would be updated."
        : "Backfill complete. {$totalUpdated} movements updated."
    );
})->purpose('Backfill stock_before/stock_after for inventory movements');

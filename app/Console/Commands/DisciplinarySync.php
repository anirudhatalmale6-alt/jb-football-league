<?php

namespace App\Console\Commands;

use App\Services\DisciplinarySyncService;
use Illuminate\Console\Command;

class DisciplinarySync extends Command
{
    protected $signature = 'disciplinary:sync';

    protected $description = 'Backfill/sync disciplinary fines from all recorded Yellow/Red card match events';

    public function handle(DisciplinarySyncService $service): int
    {
        $this->info('Syncing disciplinary fines from match events...');
        $count = $service->syncAll();
        $this->info("Done. Processed {$count} player/competition record(s).");

        return self::SUCCESS;
    }
}

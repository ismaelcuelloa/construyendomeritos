<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Console\Command;

class MarkExpiredDemos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demos:mark-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marca las órdenes demo que han expirado cambiando su estado a DEMO_EXPIRED';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando demos expirados...');

        // Buscar todas las órdenes con estado DEMO que ya expiraron
        $expiredDemos = Order::where('status_id', OrderStatus::DEMO->value)
            ->where('demo_expires_at', '<=', now())
            ->get();

        if ($expiredDemos->isEmpty()) {
            $this->info('No se encontraron demos expirados.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($expiredDemos as $demo) {
            $demo->update(['status_id' => OrderStatus::DEMO_EXPIRED->value]);
            $count++;
            
            $this->line("✓ Orden #{$demo->id} ({$demo->number}) marcada como DEMO_EXPIRED");
        }

        $this->info("✓ Total de demos marcados como expirados: {$count}");

        return Command::SUCCESS;
    }
}

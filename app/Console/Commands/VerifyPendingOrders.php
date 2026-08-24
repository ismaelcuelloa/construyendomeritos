<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerifyPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:verify-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify pending orders with ePayco API to check if they were actually paid';

    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        parent::__construct();
        $this->orderService = $orderService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando órdenes pendientes...');

        // Obtener órdenes pendientes de las últimas 24 horas
        $pendingOrders = Order::where('status_id', OrderStatus::PENDING->value)
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('No hay órdenes pendientes para verificar.');

            return 0;
        }

        $this->info("Encontradas {$pendingOrders->count()} órdenes pendientes.");

        $updated = 0;
        $failed = 0;

        foreach ($pendingOrders as $order) {
            $this->line("Verificando orden {$order->number}...");

            try {
                // Intentar verificar con ePayco
                $paymentData = $this->verifyWithEpayco($order->number);

                if ($paymentData && $paymentData['success'] && $paymentData['paid']) {
                    $this->info("  ✓ Pago confirmado para orden {$order->number}");

                    // Actualizar orden a pagada
                    $this->orderService->markOrderAsPaid($order->id);

                    // Crear suscripciones
                    $this->orderService->createSubscriptionsFromOrder($order->id);

                    $this->info('  ✓ Suscripciones creadas');
                    $updated++;
                } else {
                    $this->line("  - Orden {$order->number} aún no pagada");
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error verificando orden {$order->number}: {$e->getMessage()}");
                Log::error("Error verificando orden {$order->number}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $failed++;
            }
        }

        $this->info("\n=== Resumen ===");
        $this->info("Órdenes actualizadas: {$updated}");
        $this->info('Órdenes sin cambios: '.($pendingOrders->count() - $updated - $failed));
        if ($failed > 0) {
            $this->error("Órdenes con error: {$failed}");
        }

        return 0;
    }

    /**
     * Verify payment status with ePayco API
     */
    protected function verifyWithEpayco(string $orderNumber): ?array
    {
        $privateKey = config('services.epayco.private_key');

        if (! $privateKey) {
            throw new \Exception('ePayco private key not configured');
        }

        try {
            // Consultar el estado del pago en ePayco
            $response = Http::timeout(10)
                ->withOptions(['verify' => false]) // Desactivar verificación SSL para desarrollo
                ->withHeaders([
                    'Authorization' => 'Bearer '.$privateKey,
                ])
                ->get("https://secure.epayco.co/validation/v1/reference/{$orderNumber}");

            if (! $response->successful()) {
                Log::warning("ePayco API returned non-successful response for {$orderNumber}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();

            // Log de la respuesta para debug
            Log::info("ePayco verification response for {$orderNumber}", [
                'data' => $data,
            ]);

            // ePayco puede devolver diferentes estructuras
            if (isset($data['data'])) {
                $paymentData = $data['data'];

                return [
                    'success' => $data['success'] ?? false,
                    'paid' => strtolower($paymentData['x_response'] ?? '') === 'aceptada',
                    'reference' => $paymentData['x_ref_payco'] ?? null,
                    'amount' => $paymentData['x_amount'] ?? null,
                    'status' => $paymentData['x_response'] ?? null,
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Error calling ePayco API for {$orderNumber}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

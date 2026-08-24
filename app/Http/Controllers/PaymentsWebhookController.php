<?php

namespace App\Http\Controllers;

use App\Gateways\PaymentGateway;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentsWebhookController extends Controller
{
    protected PaymentGateway $gateway;

    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function confirmation(Request $request): \Illuminate\Http\JsonResponse
    {
        Log::info('Payment webhook received', $request->all());

        $response = $request->all();
        $paymentGateway = PaymentGateway::getInstance($response);

        try {
            $number = $paymentGateway->getReference();
            Log::info('Processing order', ['order_number' => $number]);

            $order = Order::where('number', $number)->first();
            if (! $order) {
                Log::warning('Order not found', ['order_number' => $number, 'response' => $response]);

                return response()->json(['message' => 'Order not found'], 404);
            }

            Log::info('Order found', ['order_id' => $order->id, 'current_status' => $order->status_id]);

            $paymentGateway->validate();

            if ($paymentGateway->isPaid()) {
                Log::info('Payment is PAID', ['order_number' => $number]);
                $order->status_id = \App\Enums\OrderStatus::PAID->value;
                $order->paid_at = now();
                $order->save();
                app(OrderService::class)->createSubscriptions($order);
            } elseif ($paymentGateway->isFailed()) {
                Log::info('Payment FAILED', ['order_number' => $number]);
                $order->status_id = \App\Enums\OrderStatus::FAILED->value;
                $order->save();
            } else {
                Log::info('Payment still PENDING', ['order_number' => $number]);
                $order->status_id = \App\Enums\OrderStatus::PENDING->value;
                $order->save();
            }

            Log::info('Order updated successfully', ['order_id' => $order->id, 'new_status' => $order->status_id]);

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Error processing webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $response,
            ]);

            return response()->json(['message' => 'Error processing webhook: '.$e->getMessage()], 500);
        }
    }

    public function response(Request $request)
    {
        Log::info('Payment response page accessed', $request->all());

        // Soportar múltiples pasarelas:
        // - ePayco: 'ref_payco', 'x_invoice'
        // - PayU: 'referenceCode'
        // - Wompi: 'id' (transaction ID)
        $referenceCode = $request->input('referenceCode')
            ?? $request->input('x_invoice')
            ?? $request->input('ref_payco')
            ?? $request->input('reference');

        // Para Wompi, si viene el ID de transacción, buscar por ese campo
        $transactionId = $request->input('id');

        if (! $referenceCode && ! $transactionId) {
            Log::error('No reference code or transaction ID found in payment response', $request->all());

            return redirect('/')->with('error', 'No se pudo identificar la orden');
        }

        Log::info('Looking for order', [
            'reference_code' => $referenceCode,
            'transaction_id' => $transactionId,
        ]);

        // Buscar por número de orden
        $order = Order::where('number', $referenceCode)->with(['items'])->first();

        if (! $order) {
            Log::error('Order not found for reference', [
                'reference_code' => $referenceCode,
                'transaction_id' => $transactionId,
            ]);

            return redirect('/')->with('error', 'Orden no encontrada');
        }

        return inertia('Admin/Orders/Show', ['order' => $order]);
    }

    /**
     * Webhook específico para Wompi
     * Recibe eventos de transacciones desde Wompi
     */
    public function wompiWebhook(Request $request): \Illuminate\Http\JsonResponse
    {
        // Log SUPER detallado para debugging
        Log::info('===== WOMPI WEBHOOK RECIBIDO =====');
        Log::info('Headers:', $request->headers->all());
        Log::info('Body:', $request->all());
        Log::info('Raw content:', ['content' => $request->getContent()]);

        try {
            $payload = $request->all();

            // Wompi envía los datos en una estructura específica
            // Ejemplo: { "event": "transaction.updated", "data": {...}, "signature": {...}, "timestamp": ... }
            $event = $payload['event'] ?? null;
            $transactionData = $payload['data']['transaction'] ?? null;

            if (! $event || ! $transactionData) {
                Log::error('Invalid Wompi webhook payload', ['payload' => $payload]);

                return response()->json(['message' => 'Invalid payload'], 400);
            }

            Log::info('Wompi event type', ['event' => $event]);

            // Validar firma de Wompi
            $paymentGateway = PaymentGateway::getInstance($payload);

            if (! $paymentGateway->validate()) {
                Log::error('Invalid Wompi signature');

                return response()->json(['message' => 'Invalid signature'], 401);
            }

            // Obtener la referencia (número de orden)
            $reference = $transactionData['reference'] ?? null;

            if (! $reference) {
                Log::error('No reference found in Wompi transaction', ['data' => $transactionData]);

                return response()->json(['message' => 'No reference found'], 400);
            }

            Log::info('Processing Wompi transaction', ['reference' => $reference]);

            $order = Order::where('number', $reference)->first();

            if (! $order) {
                Log::warning('Order not found for Wompi webhook', ['reference' => $reference]);

                return response()->json(['message' => 'Order not found'], 404);
            }

            Log::info('Order found', ['order_id' => $order->id, 'current_status' => $order->status_id]);

            // Actualizar estado según el status de Wompi
            $status = $transactionData['status'] ?? null;

            switch ($status) {
                case 'APPROVED':
                    Log::info('Wompi payment APPROVED', ['reference' => $reference]);
                    $order->status_id = \App\Enums\OrderStatus::PAID->value;
                    $order->paid_at = now();
                    $order->save();
                    app(OrderService::class)->createSubscriptions($order);
                    break;

                case 'DECLINED':
                case 'ERROR':
                case 'VOIDED':
                    Log::info('Wompi payment FAILED', ['reference' => $reference, 'status' => $status]);
                    $order->status_id = \App\Enums\OrderStatus::FAILED->value;
                    $order->save();
                    break;

                case 'PENDING':
                    Log::info('Wompi payment still PENDING', ['reference' => $reference]);
                    $order->status_id = \App\Enums\OrderStatus::PENDING->value;
                    $order->save();
                    break;

                default:
                    Log::warning('Unknown Wompi status', ['status' => $status, 'reference' => $reference]);
            }

            Log::info('Wompi order processed successfully', [
                'order_id' => $order->id,
                'new_status' => $order->status_id,
                'wompi_status' => $status,
            ]);

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Error processing Wompi webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json(['message' => 'Error processing webhook: '.$e->getMessage()], 500);
        }
    }
}

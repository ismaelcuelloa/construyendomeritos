<?php

namespace App\Gateways;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class WompiGateway extends Payment
{
    protected function toData(Order $order): array
    {
        // Validar que las credenciales estén configuradas
        $publicKey = config('services.wompi.public_key');

        if (empty($publicKey)) {
            throw new \Exception('Las credenciales de Wompi no están configuradas. Por favor, configure WOMPI_PUBLIC_KEY en el archivo .env');
        }

        $integritySecret = config('services.wompi.integrity_secret');
        $reference = $order->number;
        $amountInCents = (int) ($order->amount * 100); // Wompi maneja montos en centavos

        // Generar firma de integridad según documentación de Wompi
        // Concatenación: reference + amount_in_cents + currency + integrity_secret
        $concatenatedString = $reference.$amountInCents.$order->currency.$integritySecret;
        $signature = hash('sha256', $concatenatedString);

        return [
            'public-key' => $publicKey,
            'currency' => $order->currency,
            'amount-in-cents' => $amountInCents,
            'reference' => $reference,
            'signature:integrity' => $signature,
            'redirect-url' => url('/mis_cursos'),
            'customer-data:email' => auth()->user()->email,
            'customer-data:full-name' => auth()->user()->name,
        ];
    }

    protected function getCheckoutUrl(): string
    {
        // Retornar vacío para indicar que se usa el widget integrado
        return '';
    }

    public function getName(): string
    {
        return 'Wompi';
    }

    public function getCode(): string
    {
        return self::PAYMENT_METHOD_WOMPI;
    }

    public function isPaid(): bool
    {
        // Webhooks envían: data.transaction.status
        $status = $this->getResponseValue('data.transaction.status')
            ?? $this->getResponseValue('status');

        return $status === 'APPROVED';
    }

    public function isFailed(): bool
    {
        // Webhooks envían: data.transaction.status
        $status = $this->getResponseValue('data.transaction.status')
            ?? $this->getResponseValue('status');

        return in_array($status, ['DECLINED', 'ERROR', 'VOIDED']);
    }

    public function getReference(): string
    {
        // Webhooks envían: data.transaction.reference
        // Respuestas directas pueden venir en: reference
        return $this->getResponseValue('data.transaction.reference')
            ?? $this->getResponseValue('reference')
            ?? $this->getResponseValue('data.reference')
            ?? '';
    }

    /**
     * Valida el webhook de Wompi usando el secreto de eventos
     *
     * @throws \Exception
     */
    public function validate(): bool
    {
        if (! empty($this->response)) {
            return $this->validateSignature();
        } else {
            throw new \Exception('Datos de respuesta no encontrados');
        }
    }

    /**
     * Valida la firma del evento recibido desde Wompi
     *
     * @throws \Exception
     */
    protected function validateSignature(): bool
    {
        $eventsSecret = config('services.wompi.events_secret');

        // Obtener los datos del evento
        $event = $this->response['event'] ?? null;
        $signature = $this->response['signature'] ?? null;
        $timestamp = $this->response['timestamp'] ?? null;

        if (! $event || ! $signature || ! $timestamp) {
            throw new \Exception('Datos de evento incompletos');
        }

        // Verificar que el timestamp no sea muy antiguo (10 minutos para dar margen)
        $currentTime = time();
        $timeDiff = abs($currentTime - $timestamp);

        if ($timeDiff > 600) { // 10 minutos
            \Log::warning('Wompi webhook timestamp expired', [
                'timestamp' => $timestamp,
                'current_time' => $currentTime,
                'diff_seconds' => $timeDiff,
            ]);
            throw new \Exception("El evento ha expirado (diferencia: {$timeDiff} segundos)");
        }

        // Generar firma esperada
        // Según documentación de Wompi: se concatenan las propiedades especificadas en signature.properties
        // y se obtienen del objeto data.transaction
        $properties = $signature['properties'] ?? [];
        $transaction = $this->response['data']['transaction'] ?? [];

        $concatenatedValues = '';
        foreach ($properties as $property) {
            // Las propiedades vienen como "transaction.id", "transaction.status", etc.
            $propertyName = str_replace('transaction.', '', $property);
            $value = $transaction[$propertyName] ?? '';
            $concatenatedValues .= $value;
        }

        // Fórmula: concatenatedValues + timestamp + eventsSecret
        $stringToHash = $concatenatedValues.$timestamp.$eventsSecret;
        $expectedChecksum = hash('sha256', $stringToHash);

        \Log::info('Wompi signature validation', [
            'properties' => $properties,
            'concatenated' => $concatenatedValues,
            'timestamp' => $timestamp,
            'expected_checksum' => $expectedChecksum,
            'received_checksum' => $signature['checksum'],
            'match' => hash_equals($expectedChecksum, $signature['checksum']),
        ]);

        if (hash_equals($expectedChecksum, $signature['checksum'])) {
            return true;
        } else {
            throw new \Exception('Firma de evento inválida');
        }
    }

    /**
     * Consulta el estado de una transacción en Wompi
     */
    public function getTransactionStatus(string $transactionId): array
    {
        $privateKey = config('services.wompi.private_key');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$privateKey,
        ])->get("https://production.wompi.co/v1/transactions/{$transactionId}");

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error al consultar el estado de la transacción: '.$response->body());
    }
}

<?php

namespace App\Gateways;

use App\Models\Order;

class EPaycoGateway extends Payment
{
    protected function toData(Order $order): array
    {
        // Validar que las credenciales estén configuradas
        $publicKey = config('services.epayco.public_key');

        if (empty($publicKey)) {
            throw new \Exception('Las credenciales de ePayco no están configuradas. Por favor, configure EPAYCO_PUBLIC_KEY en el archivo .env');
        }

        return [
            'key' => $publicKey,
            'invoice' => $order->number,
            'amount' => $order->amount,
            'name' => 'Compra de cursos',
            'description' => 'Orden '.$order->number,
            'currency' => $order->currency,
            'country' => 'CO',
            'test' => config('services.epayco.test') ? 'true' : 'false',
            'external' => 'false',
            'response' => url('/payments/status'),
            'confirmation' => url('/payments/confirmation'),
        ];
    }

    protected function getCheckoutUrl(): string
    {
        return 'https://secure.epayco.co/checkout/v2/payment';
    }

    public function getName(): string
    {
        return 'EPayco';
    }

    public function getCode(): string
    {
        return self::PAYMENT_METHOD_EPAYCO;
    }

    public function isPaid(): bool
    {
        return $this->getResponseValue('x_cod_response') == 1;
    }

    public function isFailed(): bool
    {
        return $this->getResponseValue('x_cod_response') == 2;
    }

    public function getReference(): string
    {
        return $this->getResponseValue('x_id_invoice');
    }

    /**
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
     * @throws \Exception
     */
    public function validateSignature(): bool
    {
        $p_cust_id_cliente = config('services.epayco.client_id');
        $p_key = config('services.epayco.client_secret');

        $x_ref_payco = $this->getResponseValue('x_ref_payco');
        $x_transaction_id = $this->getResponseValue('x_transaction_id');
        $x_amount = $this->getResponseValue('x_amount');
        $x_currency_code = $this->getResponseValue('x_currency_code');
        $x_signature = $this->getResponseValue('x_signature');

        $signature = hash(
            'sha256',
            implode('^', [
                $p_cust_id_cliente,
                $p_key,
                $x_ref_payco,
                $x_transaction_id,
                $x_amount,
                $x_currency_code,
            ])
        );

        if ($signature === $x_signature) {
            return true;
        } else {
            throw new \Exception('Invalid signature');
        }
    }
}

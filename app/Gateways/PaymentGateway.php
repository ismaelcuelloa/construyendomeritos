<?php

namespace App\Gateways;

class PaymentGateway
{
    public static function getInstance(array $response = []): Payment
    {
        return self::create(self::getDefaultPaymentMethod(), $response);
    }

    public static function getDefaultPaymentMethod(): string
    {
        // Cambio a Wompi como pasarela por defecto
        return env('DEFAULT_PAYMENT_METHOD', (new WompiGateway)->getCode());
    }

    private static function create(string $paymentMethod, array $response = []): Payment
    {
        return match ($paymentMethod) {
            // Payment::PAYMENT_METHOD_EPAYCO => new EPaycoGateway($response), // Desactivado
            Payment::PAYMENT_METHOD_PAYU => new PayUGateway($response),
            Payment::PAYMENT_METHOD_WOMPI => new WompiGateway($response),
            default => throw new \InvalidArgumentException('Invalid payment method'),
        };
    }
}

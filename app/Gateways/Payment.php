<?php

namespace App\Gateways;

use App\Models\Order;

abstract class Payment implements PaymentInterface
{
    protected array $response = [];

    const PAYMENT_METHOD_PAYU = 'payu';

    const PAYMENT_METHOD_EPAYCO = 'epayco';

    const PAYMENT_METHOD_WOMPI = 'wompi';

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    public function createPaymentRequest(Order $order): array
    {
        return [
            'order' => $order,
            'data' => $this->toData($order),
            'url' => $this->getCheckoutUrl(),
            'method' => $this->getCode(),
            'name' => $this->getName(),
        ];
    }

    protected function getResponseValue(string $key): mixed
    {
        return $this->response[$key] ?? null;
    }

    abstract protected function toData(Order $order): array;

    abstract protected function getCheckoutUrl(): string;

    abstract public function isPaid(): bool;

    abstract public function isFailed(): bool;

    abstract public function getReference(): string;
}

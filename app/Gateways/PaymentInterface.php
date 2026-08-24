<?php

namespace App\Gateways;

use App\Models\Order;

interface PaymentInterface
{
    public function createPaymentRequest(Order $order): array;

    public function getName(): string;

    public function getCode(): string;

    public function isPaid(): bool;

    public function isFailed(): bool;

    public function getReference(): string;

    /**
     * @throws \Exception
     */
    public function validate(): bool;
}

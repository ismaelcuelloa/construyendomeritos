<?php

namespace App\Gateways;

use App\Models\Order;
use Illuminate\Support\Facades\Config;

class PayUGateway extends Payment
{
    protected string $apiKey;

    protected string $apiLogin;

    protected string $merchantId;

    protected string $accountId;

    protected string $mode;

    protected string $baseUrl;

    protected string $responseUrl;

    protected string $confirmationUrl;

    public function __construct(array $response = [])
    {
        $config = Config::get('services.payu');

        $this->apiKey = $config['api_key'];
        $this->apiLogin = $config['api_login'];
        $this->merchantId = $config['merchant_id'];
        $this->accountId = $config['account_id'];
        $this->mode = $config['mode'];
        $this->baseUrl = $config['base_url'];
        $this->responseUrl = $config['response_url'];
        $this->confirmationUrl = $config['confirmation_url'];

        return @parent::__construct($response);

    }

    /**
     * Genera la firma SHA-256 para PayU.
     */
    public function generateSignature(string $referenceCode, float $amount, string $currency): string
    {
        $signatureString = "{$this->apiKey}~{$this->merchantId}~{$referenceCode}~{$amount}~{$currency}";

        return md5($signatureString);
    }

    /**
     * Genera el payload de la transacción.
     */
    protected function toData(Order $order): array
    {

        $referenceCode = $order->number;
        $amount = number_format($order->amount, 2, '.', '');
        $currency = $order->currency ?? 'COP';
        $buyerEmail = auth()->user()->email;
        $description = 'Compra de cursos';

        $signature = $this->generateSignature($referenceCode, $amount, $currency);

        return [
            'merchantId' => $this->merchantId,
            'accountId' => $this->accountId,
            'description' => $description,
            'referenceCode' => $referenceCode,
            'amount' => $amount,
            'currency' => $currency,
            'signature' => $signature,
            'buyerEmail' => $buyerEmail,
            'responseUrl' => $this->responseUrl,
            'confirmationUrl' => $this->confirmationUrl,
            'test' => $this->mode === 'sandbox' ? 1 : 0,
        ];
    }

    /**
     * URL del formulario de pago.
     * PayU recomienda redirigir a: https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/
     */
    public function getCheckoutUrl(): string
    {
        return $this->mode === 'sandbox'
            ? 'https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu'
            : 'https://checkout.payulatam.com/ppp-web-gateway-payu';
    }

    /**
     * Valida firma recibida en la notificación (IPN).
     */
    public function validateSignature(): bool
    {
        $expectedSignatureString = "{$this->apiKey}~{$this->merchantId}~{$this->getResponseValue('reference_sale')}~{$this->getResponseValue('value')}~{$this->getResponseValue('currency')}~{$this->getResponseValue('state_pol')}";
        $expectedSignature = md5($expectedSignatureString);

        return strcasecmp($expectedSignature, $this->getResponseValue('sign')) === 0;
    }

    public function getName(): string
    {
        return 'PayU';
    }

    public function getCode(): string
    {
        return self::PAYMENT_METHOD_PAYU;
    }

    public function isPaid(): bool
    {
        return $this->getResponseValue('state_pol') == '4';
    }

    public function isFailed(): bool
    {
        return $this->getResponseValue('state_pol') == '6';
    }

    public function getReference(): string
    {
        return $this->getResponseValue('reference_sale');
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
}

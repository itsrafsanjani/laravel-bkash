<?php

namespace ItsRafsanJani\Bkash\Data;

use Ramsey\Uuid\Type\Decimal;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class CreatePaymentData extends Data
{
    public string|Optional $callbackURL;
    public string|Optional $merchantInvoiceNumber;

    public function __construct(
        public string $mode = '0011', // This parameter indicates the mode of payment. For Tokenized Checkout, the value of this parameter should be "0001".,
        public string $payerReference,
        public float  $amount,
        public string $currency = 'BDT',
        public string $intent = 'Sale',
    )
    {
        $this->callbackURL = config('bkash.callbackURL');
        $this->merchantInvoiceNumber = $this->payerReference;
    }
}

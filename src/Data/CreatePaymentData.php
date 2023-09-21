<?php

namespace ItsRafsanJani\Bkash\Data;

use Spatie\LaravelData\Data;

class CreatePaymentData extends Data
{
    public string $callbackURL;

    public string $merchantInvoiceNumber;

    public function __construct(
        public float $amount,
        public string $payerReference,
        public string $mode = '0011', // This parameter indicates the mode of payment. For Tokenized Checkout, the value of this parameter should be "0001".,
        public string $currency = 'BDT',
        public string $intent = 'sale',
    ) {
        $this->callbackURL = config('bkash.callbackURL');
        $this->merchantInvoiceNumber = $this->payerReference;
    }
}

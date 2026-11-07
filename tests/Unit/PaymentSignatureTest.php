<?php

namespace Tests\Unit;

use App\Services\PaymentService;
use Tests\TestCase;

class PaymentSignatureTest extends TestCase
{
    public function test_midtrans_signature_uses_raw_gross_amount(): void
    {
        config(['pangkonan.midtrans.server_key' => 'server-key']);
        $signature = hash('sha512', 'ORDER-120010000.00server-key');

        $this->assertTrue(app(PaymentService::class)->validSignature('ORDER-1', '200', '10000.00', $signature));
        $this->assertFalse(app(PaymentService::class)->validSignature('ORDER-1', '200', '10000', $signature));
    }
}

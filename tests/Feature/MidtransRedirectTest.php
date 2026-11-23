<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Services\MidtransGateway;
use Illuminate\Database\Eloquent\Collection;
use Midtrans\Snap;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class MidtransRedirectTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_snap_session_returns_to_the_order_on_the_configured_domain(): void
    {
        config(['app.url' => 'https://shop.example.test/']);
        $order = new Order([
            'order_number' => 'PGK-TEST-REDIRECT', 'total' => 25000, 'delivery_fee' => 0,
            'recipient_name' => 'Pelanggan', 'recipient_phone' => '08123456789',
        ]);
        $order->setRelation('user', User::factory()->make());
        $order->setRelation('items', new Collection([new OrderItem([
            'sku' => 'TEST-001', 'product_name' => 'Produk', 'unit_price' => 25000, 'quantity' => 1,
        ])]));
        $payment = new Payment(['provider_order_id' => 'PGK-TEST-REDIRECT-A1']);
        $snap = Mockery::mock('alias:'.Snap::class);
        $snap->shouldReceive('createTransaction')->once()->with(Mockery::on(
            fn (array $payload): bool => $payload['callbacks'] === [
                'finish' => 'https://shop.example.test/pesanan/PGK-TEST-REDIRECT',
                'error' => 'https://shop.example.test/pesanan/PGK-TEST-REDIRECT',
            ] && $payload['transaction_details']['order_id'] === 'PGK-TEST-REDIRECT-A1',
        ))->andReturn((object) ['token' => 'test-token', 'redirect_url' => 'https://app.sandbox.midtrans.com/test']);

        $result = app(MidtransGateway::class)->createTransaction($order, $payment);

        $this->assertSame(['token' => 'test-token', 'redirect_url' => 'https://app.sandbox.midtrans.com/test'], $result);
    }
}

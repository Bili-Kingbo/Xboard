<?php

namespace Tests\Unit;

use App\Http\Controllers\V1\Client\ClientController;
use App\Utils\Helper;
use Illuminate\Http\Request;
use ReflectionMethod;
use Tests\TestCase;

class InternalClashSubscriptionTest extends TestCase
{
    public function test_internal_subscribe_url_carries_meta_flag(): void
    {
        config(['app.internal_free_mode' => true]);

        $this->assertStringEndsWith(
            '/s/test-token?flag=meta',
            Helper::getSubscribeUrl('test-token'),
        );
    }

    public function test_internal_subscription_defaults_to_meta_without_a_flag(): void
    {
        config(['app.internal_free_mode' => true]);

        $clientInfo = $this->getClientInfo(Request::create('/s/test-token', 'GET', server: [
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]));

        $this->assertSame('meta', $clientInfo['flag']);
        $this->assertSame('meta', $clientInfo['name']);
    }

    public function test_explicit_subscription_flag_still_takes_precedence(): void
    {
        config(['app.internal_free_mode' => true]);

        $clientInfo = $this->getClientInfo(Request::create('/s/test-token', 'GET', [
            'flag' => 'clashmetaforandroid',
        ]));

        $this->assertSame('clashmetaforandroid', $clientInfo['flag']);
        $this->assertSame('clashmetaforandroid', $clientInfo['name']);
    }

    private function getClientInfo(Request $request): array
    {
        $method = new ReflectionMethod(ClientController::class, 'getClientInfo');
        $method->setAccessible(true);

        return $method->invoke(new ClientController(), $request);
    }
}

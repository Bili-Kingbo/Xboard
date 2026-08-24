<?php

namespace Tests\Unit\Services\Auth;

use App\Models\ServerGroup;
use App\Models\Server;
use App\Models\User;
use App\Services\Auth\RegisterService;
use App\Services\ServerService;
use App\Utils\CacheKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RegisterServiceTest extends TestCase
{
    use RefreshDatabase;

    private RegisterService $service;
    private int $groupId;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.stores.redis' => ['driver' => 'array'],
            'app.internal_free_mode' => true,
            'app.internal_free_default_user_transfer_gb' => 0,
            'app.settings_cache_store' => 'array',
        ]);
        app()->forgetScopedInstances();

        Cache::flush();
        admin_setting([
            'email_verify' => 1,
            'email_whitelist_enable' => 0,
            'email_gmail_limit_enable' => 0,
            'stop_register' => 0,
            'invite_force' => 0,
            'captcha_enable' => 0,
            'register_limit_by_ip_enable' => 0,
        ]);

        $group = new ServerGroup();
        $group->name = 'Engineering';
        $group->save();
        $this->groupId = $group->id;

        $this->service = app(RegisterService::class);
    }

    public function test_validate_register_requires_an_existing_identity_group(): void
    {
        [$missingSuccess, $missingResult] = $this->service->validateRegister($this->makeRequest([
            'group_id' => null,
        ]));
        [$unknownSuccess, $unknownResult] = $this->service->validateRegister($this->makeRequest([
            'group_id' => 999999,
        ]));

        $this->assertFalse($missingSuccess);
        $this->assertSame(422, $missingResult[0]);
        $this->assertFalse($unknownSuccess);
        $this->assertSame(422, $unknownResult[0]);
    }

    public function test_register_assigns_free_access_for_the_selected_group(): void
    {
        admin_setting(['email_verify' => 0]);

        [$success, $user] = $this->service->register($this->makeRequest());

        $this->assertTrue($success);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($this->groupId, $user->group_id);
        $this->assertNull($user->plan_id);
        $this->assertNull($user->expired_at);
        $this->assertSame(0, $user->transfer_enable);
        $this->assertSame(0, $user->getEffectiveTransferEnable());
        $this->assertTrue($user->hasUnlimitedTraffic());
        $this->assertTrue($user->isActive());
        $this->assertTrue($user->isAvailable());
    }

    public function test_zero_personal_traffic_means_unlimited_and_positive_value_is_a_limit(): void
    {
        $user = new User();
        $user->group_id = $this->groupId;

        $user->transfer_enable = 0;
        $user->u = 900 * 1073741824;
        $this->assertTrue($user->hasUnlimitedTraffic());
        $this->assertSame(0, $user->getRemainingTraffic());

        $user->transfer_enable = 1000 * 1073741824;
        $this->assertFalse($user->hasUnlimitedTraffic());
        $this->assertSame(100 * 1073741824, $user->getRemainingTraffic());
    }

    public function test_node_user_sync_treats_zero_as_unlimited(): void
    {
        admin_setting(['email_verify' => 0]);
        [, $user] = $this->service->register($this->makeRequest());
        $node = new Server(['group_ids' => [(string) $this->groupId]]);

        $user->forceFill([
            'transfer_enable' => 0,
            'u' => 600 * 1073741824,
            'd' => 0,
        ])->save();
        $this->assertTrue(ServerService::getAvailableUsers($node)->contains('id', $user->id));

        $user->forceFill(['transfer_enable' => 500 * 1073741824])->save();
        $this->assertFalse(ServerService::getAvailableUsers($node)->contains('id', $user->id));

        $user->forceFill(['transfer_enable' => 700 * 1073741824])->save();
        $this->assertTrue(ServerService::getAvailableUsers($node)->contains('id', $user->id));
    }

    public function test_validate_register_rejects_missing_cached_email_code(): void
    {
        [$success, $result] = $this->service->validateRegister($this->makeRequest([
            'email_code' => '123456',
        ]));

        $this->assertFalse($success);
        $this->assertSame(400, $result[0]);
    }

    public function test_validate_register_rejects_boolean_email_code(): void
    {
        [$success, $result] = $this->service->validateRegister($this->makeRequest([
            'email_code' => false,
        ]));

        $this->assertFalse($success);
        $this->assertSame(422, $result[0]);
    }

    public function test_validate_register_accepts_matching_cached_email_code(): void
    {
        Cache::put(CacheKey::get('EMAIL_VERIFY_CODE', 'user@example.com'), 123456, 300);

        [$success, $result] = $this->service->validateRegister($this->makeRequest([
            'email_code' => '123456',
        ]));

        $this->assertTrue($success);
        $this->assertNull($result);
    }

    private function makeRequest(array $overrides = []): Request
    {
        return Request::create('/api/v1/passport/auth/register', 'POST', array_merge([
            'email' => 'user@example.com',
            'password' => 'password123',
            'group_id' => $this->groupId,
        ], $overrides));
    }
}

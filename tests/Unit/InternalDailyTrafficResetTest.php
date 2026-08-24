<?php

namespace Tests\Unit;

use App\Console\Kernel;
use App\Jobs\NodeUserSyncJob;
use App\Models\TrafficResetLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InternalDailyTrafficResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.internal_free_mode' => true,
            'app.settings_cache_store' => 'array',
            'cache.default' => 'array',
        ]);
        app()->forgetScopedInstances();
        Cache::flush();
        Queue::fake();
        Carbon::setTestNow(Carbon::parse('2026-08-24 00:00:05', 'Asia/Shanghai'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_enabled_command_resets_used_traffic_once_per_beijing_day(): void
    {
        admin_setting(['internal_daily_traffic_reset_enable' => true]);

        $usedUser = $this->createUser('used@example.com', 1024, 2048);
        $idleUser = $this->createUser('idle@example.com', 0, 0);

        $this->artisan('internal:reset-daily-traffic')->assertSuccessful();

        $usedUser->refresh();
        $idleUser->refresh();
        $this->assertSame(0, $usedUser->u);
        $this->assertSame(0, $usedUser->d);
        $this->assertSame(1, $usedUser->reset_count);
        $this->assertSame(0, $idleUser->reset_count);
        $this->assertSame('2026-08-24', admin_setting('internal_daily_traffic_reset_last_date'));

        $log = TrafficResetLog::where('user_id', $usedUser->id)->sole();
        $this->assertSame(TrafficResetLog::TYPE_DAILY, $log->reset_type);
        $this->assertSame(TrafficResetLog::SOURCE_CRON, $log->trigger_source);
        $this->assertSame('Asia/Shanghai', $log->metadata['timezone']);
        $this->assertSame('2026-08-24', $log->metadata['reset_date']);

        $usedUser->forceFill(['u' => 4096])->save();
        $this->artisan('internal:reset-daily-traffic')->assertSuccessful();
        $this->assertSame(4096, $usedUser->fresh()->u);
        $this->assertSame(1, TrafficResetLog::where('user_id', $usedUser->id)->count());

        Queue::assertPushed(NodeUserSyncJob::class);
    }

    public function test_disabled_command_does_not_reset_traffic(): void
    {
        admin_setting(['internal_daily_traffic_reset_enable' => false]);
        $user = $this->createUser('disabled@example.com', 1024, 0);

        $this->artisan('internal:reset-daily-traffic')->assertSuccessful();

        $this->assertSame(1024, $user->fresh()->u);
        $this->assertDatabaseCount('v2_traffic_reset_logs', 0);
    }

    public function test_scheduler_runs_command_at_beijing_midnight(): void
    {
        $schedule = app(Kernel::class)->resolveConsoleSchedule();
        $event = collect($schedule->events())
            ->first(fn ($event) => str_contains($event->command ?? '', 'internal:reset-daily-traffic'));

        $this->assertNotNull($event);
        $this->assertSame('0 0 * * *', $event->expression);
        $this->assertSame('Asia/Shanghai', $event->timezone);
    }

    private function createUser(string $email, int $upload, int $download): User
    {
        return User::create([
            'email' => $email,
            'password' => 'password',
            'uuid' => fake()->uuid(),
            'token' => fake()->unique()->bothify('????????????????????????????????'),
            'group_id' => 1,
            'transfer_enable' => 0,
            'u' => $upload,
            'd' => $download,
            'banned' => false,
        ]);
    }
}

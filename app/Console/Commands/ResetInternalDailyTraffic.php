<?php

namespace App\Console\Commands;

use App\Services\TrafficResetService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ResetInternalDailyTraffic extends Command
{
    private const TIMEZONE = 'Asia/Shanghai';
    private const LAST_DATE_SETTING = 'internal_daily_traffic_reset_last_date';
    private const LAST_AT_SETTING = 'internal_daily_traffic_reset_last_at';

    protected $signature = 'internal:reset-daily-traffic';

    protected $description = '北京时间每天零点重置内部用户已用流量';

    public function __construct(
        private readonly TrafficResetService $trafficResetService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!config('app.internal_free_mode')) {
            $this->info('Internal free mode is disabled; daily traffic reset skipped.');
            return self::SUCCESS;
        }

        if (!(bool) admin_setting('internal_daily_traffic_reset_enable', false)) {
            $this->info('Daily traffic reset is disabled.');
            return self::SUCCESS;
        }

        $beijingNow = Carbon::now(self::TIMEZONE);
        $resetDate = $beijingNow->toDateString();

        if ((string) admin_setting(self::LAST_DATE_SETTING, '') === $resetDate) {
            $this->info("Traffic for {$resetDate} has already been reset.");
            return self::SUCCESS;
        }

        $lock = Cache::lock("internal_daily_traffic_reset:{$resetDate}", 3600);
        if (!$lock->get()) {
            $this->info('Another daily traffic reset task is already running.');
            return self::SUCCESS;
        }

        try {
            if ((string) admin_setting(self::LAST_DATE_SETTING, '') === $resetDate) {
                return self::SUCCESS;
            }

            $result = $this->trafficResetService->resetInternalUsersDaily($beijingNow);
            if ($result['error_count'] > 0) {
                Log::error('Internal daily traffic reset completed with errors', $result);
                $this->error("Daily traffic reset failed for {$result['error_count']} users.");
                return self::FAILURE;
            }

            admin_setting([
                self::LAST_DATE_SETTING => $resetDate,
                self::LAST_AT_SETTING => $beijingNow->timestamp,
            ]);

            Log::info('Internal daily traffic reset completed', $result + [
                'reset_date' => $resetDate,
                'timezone' => self::TIMEZONE,
            ]);

            $this->info("Reset {$result['total_reset']} users for {$resetDate} (Beijing time).");
            return self::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}

<?php

namespace App\Providers;

use App\jobs\CleanupDraftExpensesJob;
use App\jobs\DetectOrphanTransactionsJob;
use App\jobs\ProcessAutoReconciliationJob;
use App\jobs\SyncBridgeTransactionsJob;
use App\jobs\VerifyAllDigitalSealsJob;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command('app:weekly-summary')
                ->fridays()
                ->at('17:00');

            $schedule->command('app:generate-reports')
                ->monthlyOn(1, '02:00');

            $schedule->job(CleanupDraftExpensesJob::class)
                ->daily()
                ->at('01:00');

            $schedule->job(new ProcessAutoReconciliationJob)
                ->dailyAt('02:00');

            $schedule->job(new DetectOrphanTransactionsJob)
                ->weeklyOn(1, '08:00');

            $schedule->job(new VerifyAllDigitalSealsJob)
                ->dailyAt('03:00');

            $schedule->job(new SyncBridgeTransactionsJob)
                ->dailyAt('04:00');
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

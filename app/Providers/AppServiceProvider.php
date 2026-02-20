<?php

namespace App\Providers;

use App\Services\Firebase\FirebaseProjectManager as AppFirebaseProjectManager;
use Carbon\CarbonImmutable;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Kreait\Laravel\Firebase\FirebaseProjectManager;
use NotificationChannels\Fcm\FcmChannel;

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
        $this->app->singleton(FirebaseProjectManager::class, AppFirebaseProjectManager::class);

        Event::listen(NotificationSending::class, function (NotificationSending $event) {
            if ($event->channel !== FcmChannel::class) {
                return;
            }
            $tokens = Arr::wrap($event->notifiable->routeNotificationFor('fcm', $event->notification));
            if (empty($tokens) || (is_array($tokens) && empty(array_filter($tokens)))) {
                Log::warning('FCM not sent: member has no FCM token. Register token via POST /api/member/fcm-token', [
                    'notifiable_id' => $event->notifiable->id ?? null,
                    'notifiable_type' => get_class($event->notifiable),
                ]);
            }
        });

        $this->configureDefaults();
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
            : null
        );
    }
}

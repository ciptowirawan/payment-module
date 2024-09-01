<?php

namespace App\Providers;

use App\Listeners\SyncMemberData;
use App\Events\MemberDataReceived;
use App\Listeners\SyncRegistrantData;
use Illuminate\Support\Facades\Event;
use App\Events\RegistrantDataReceived;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\LogSuccessfulLogin::class,
        ],
        RegistrantDataReceived::class => [
            SyncRegistrantData::class,
        ],
        MemberDataReceived::class => [
            SyncMemberData::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

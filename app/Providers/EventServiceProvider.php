<?php

namespace App\Providers;

use App\Events\WalletEvent;
use App\Events\WalletDebitEvent;
use App\Listeners\WalletListener;
use App\Events\FlexWalletDebitEvent;
use App\Listeners\WalletDebitListener;
use App\Listeners\FlexWalletDebitListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        WalletEvent::class => [
            WalletListener::class
        ],
        WalletDebitEvent::class => [
            WalletDebitListener::class
        ],
        FlexWalletDebitEvent::class => [
            FlexWalletDebitListener::class
        ],
    ];
}

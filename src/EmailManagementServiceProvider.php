<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Prajwal89\EmailManagement\Commands\CreateEmailCampaignCommand;
use Prajwal89\EmailManagement\Commands\CreateEmailEventCommand;
use Prajwal89\EmailManagement\Commands\CreateEmailVariantCommand;
use Prajwal89\EmailManagement\Commands\CreateReceivableGroupCommand;
use Prajwal89\EmailManagement\Commands\ScanMailboxCommand;
use Prajwal89\EmailManagement\Commands\SeedEmailsDatabaseCommand;
use Prajwal89\EmailManagement\Listeners\MessageSendingListener;
use Prajwal89\EmailManagement\Listeners\MessageSentListener;

class EmailManagementServiceProvider extends ServiceProvider
{
    public function register() {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->mergeConfigFrom(__DIR__ . '/../config/email-management.php', 'email-management');

        $this->loadViewsFrom(config('email-management.view_dir'), 'email-management');

        $this->publishes([
            __DIR__ . '/../resources/views' => config('email-management.view_dir'),
        ], 'email-management-views');

        $this->publishes([
            __DIR__ . '/../config/email-management.php' => config_path('email-management.php'),
        ], 'email-management-config');

        Event::listen(MessageSending::class, MessageSendingListener::class);

        Event::listen(MessageSent::class, MessageSentListener::class);

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            $this->commands([
                CreateEmailEventCommand::class,
                CreateEmailCampaignCommand::class,
                SeedEmailsDatabaseCommand::class,
                CreateReceivableGroupCommand::class,
                ScanMailboxCommand::class,
                CreateEmailVariantCommand::class,
            ]);
        }
    }
}

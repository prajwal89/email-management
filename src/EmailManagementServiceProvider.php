<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement;

use DirectoryTree\ImapEngine\Laravel\Events\MessageReceived;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Prajwal89\EmailManagement\Commands\CreateEmailCampaignCommand;
use Prajwal89\EmailManagement\Commands\CreateEmailEventCommand;
use Prajwal89\EmailManagement\Commands\CreateEmailVariantCommand;
use Prajwal89\EmailManagement\Commands\CreateFollowUpCommand;
use Prajwal89\EmailManagement\Commands\CreateReceivableGroupCommand;
use Prajwal89\EmailManagement\Listeners\HoneypotDetectedASpanListener;
use Prajwal89\EmailManagement\Listeners\MessageSendingListener;
use Prajwal89\EmailManagement\Listeners\MessageSentListener;
use Prajwal89\EmailManagement\Listeners\NewEmailReceivedListener;
use Spatie\Honeypot\Events\SpamDetectedEvent;

// todo use spaties package skeleton
class EmailManagementServiceProvider extends ServiceProvider
{
    public function register() {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->mergeConfigFrom(__DIR__ . '/../config/email-management.php', 'email-management');

        // for internal package views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'em');

        // for views generated by the users
        $this->loadViewsFrom(config('email-management.view_dir'), 'email-management');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/email-management.php' => config_path('email-management.php'),
            ], 'email-management-config');


            $this->loadMigrationsFrom([
                config('email-management.migrations_dir'), // user generated migrations
            ]);

            if ($this->app->runningInConsole()) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/2024_09_08_150057_create_all_tables.php' =>
                    database_path('migrations/' . date('Y_m_d_His') . '_create_email_management_tables.php'),
                ], 'email-management-migrations');
            }

            $this->commands([
                CreateEmailEventCommand::class,
                CreateEmailCampaignCommand::class,
                CreateReceivableGroupCommand::class,
                CreateEmailVariantCommand::class,
                CreateFollowUpCommand::class,
            ]);
        }


        Factory::guessFactoryNamesUsing(function (string $modelName) {
            if (str_starts_with($modelName, 'Prajwal89\\EmailManagement\\')) {
                return 'Prajwal89\\EmailManagement\\Database\\Factories\\' . class_basename($modelName) . 'Factory';
            }

            return 'Database\\Factories\\' . class_basename($modelName) . 'Factory'; // default for app models
        });

        $this->configListeners();
    }

    public function configListeners()
    {
        Event::listen(MessageSending::class, MessageSendingListener::class);

        Event::listen(MessageSent::class, MessageSentListener::class);

        Event::listen(MessageReceived::class, NewEmailReceivedListener::class);

        Event::listen(SpamDetectedEvent::class, HoneypotDetectedASpanListener::class);
    }
}

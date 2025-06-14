<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * php artisan em:seed-db
 */
class SeedEmailsDatabaseCommand extends Command
{
    protected $signature = 'em:seed-db';

    protected $description = 'Seed the email events table';

    public function handle(): void
    {
        $this->seedEmailEvents();
        $this->seedEmailCampaigns();
        $this->seedEmailVariants();
    }

    public function seedEmailEvents()
    {
        $this->info('Seeding: Email Events');

        $directory = config('email-management.seeders_dir') . '/EmailEvents';

        if (!File::isDirectory($directory)) {
            return;
        }

        $allFiles = File::allFiles($directory);

        foreach ($allFiles as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            $fullClassName = 'Database\\Seeders\\EmailManagement\\EmailEvents\\' . $className;

            if (class_exists($fullClassName)) {
                (new $fullClassName)->run();
            } else {
                $this->fail("Class {$fullClassName} does not exist.");
            }
        }

        $this->info('Seeded email events');
    }

    public function seedEmailCampaigns()
    {
        $this->info('Seeding: Email Campaigns');

        $directory = config('email-management.seeders_dir') . '/EmailCampaigns';

        if (!File::isDirectory($directory)) {
            return;
        }

        $allFiles = File::allFiles($directory);

        foreach ($allFiles as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            $fullClassName = 'Database\\Seeders\\EmailManagement\\EmailCampaigns\\' . $className;

            if (class_exists($fullClassName)) {
                (new $fullClassName)->run();
            } else {
                $this->fail("Class {$fullClassName} does not exist.");
            }
        }
    }

    public function seedEmailVariants()
    {
        $this->info('Seeding: Email Variants');

        $directory = config('email-management.seeders_dir') . '/EmailVariants';

        if (!File::isDirectory($directory)) {
            return;
        }

        $allFiles = File::allFiles($directory);

        foreach ($allFiles as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            $fullClassName = 'Database\\Seeders\\EmailManagement\\EmailVariants\\' . $className;

            if (class_exists($fullClassName)) {
                (new $fullClassName)->run();
            } else {
                $this->fail("Class {$fullClassName} does not exist.");
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use File;
use Illuminate\Console\Command;

// php artisan email-management:seed-db
class SeedEmailsDatabaseCommand extends Command
{
    protected $signature = 'email-management:seed-db';

    protected $description = 'Seed the email events table';

    public function handle(): void
    {
        $this->seedEmailEvents();
        // $this->seedEmailCampaigns();
    }

    public function seedEmailEvents()
    {
        $this->info('Seeding email events');

        $allFiles = File::allFiles(config('email-management.seeders_dir') . '/EmailEvents');

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
        $allFiles = File::allFiles(config('email-management.seeders_dir') . '/EmailCampaigns');

        foreach ($allFiles as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            $fullClassName = __NAMESPACE__ . '\\EmailCampaigns\\' . $className;

            if (class_exists($fullClassName)) {
                (new $fullClassName)->run();
            } else {
                $this->fail("Class {$fullClassName} does not exist.");
            }
        }
    }
}

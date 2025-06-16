<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use SplFileInfo;

/**
 * php artisan em:seed-db
 * 
 * todo: resolve folders from central location
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

        $groupedFiles = collect($allFiles)->groupBy(function (SplFileInfo $file) {
            return str($file->getFilename())->endsWith('DeleteSeeder.php') ? 'delete' : 'create';
        })
            ->sortBy(function ($_, $key) {
                return $key === 'create' ? 0 : 1;
            });

        // seed create files first then run delete seeders
        foreach ($groupedFiles as $groupFiles) {
            foreach ($groupFiles as $file) {
                $fullClassName = 'Database\\Seeders\\EmailManagement\\EmailEvents\\' . $file->getBasename('.php');

                if (class_exists($fullClassName)) {
                    (new $fullClassName)->run();
                } else {
                    $this->fail("Class {$fullClassName} does not exist.");
                }
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

        $groupedFiles = collect($allFiles)->groupBy(function (SplFileInfo $file) {
            return str($file->getFilename())->endsWith('DeleteSeeder.php') ? 'delete' : 'create';
        })
            ->sortBy(function ($_, $key) {
                return $key === 'create' ? 0 : 1;
            });

        // seed create files first then run delete seeders
        foreach ($groupedFiles as $groupFiles) {
            foreach ($groupFiles as $file) {
                $fullClassName = 'Database\\Seeders\\EmailManagement\\EmailCampaigns\\' . $file->getBasename('.php');

                if (class_exists($fullClassName)) {
                    (new $fullClassName)->run();
                } else {
                    $this->fail("Class {$fullClassName} does not exist.");
                }
            }
        }

        $this->info('Seeded email campaigns');
    }

    public function seedEmailVariants()
    {
        $this->info('Seeding: Email Variants');

        $directory = config('email-management.seeders_dir') . '/EmailVariants';

        if (!File::isDirectory($directory)) {
            return;
        }

        $allFiles = File::allFiles($directory);

        $groupedFiles = collect($allFiles)->groupBy(function (SplFileInfo $file) {
            return str($file->getFilename())->endsWith('DeleteSeeder.php') ? 'delete' : 'create';
        })
            ->sortBy(function ($_, $key) {
                return $key === 'create' ? 0 : 1;
            });

        // seed create files first then run delete seeders
        foreach ($groupedFiles as $groupFiles) {
            foreach ($groupFiles as $file) {
                $fullClassName = 'Database\\Seeders\\EmailManagement\\EmailVariants\\' . $file->getBasename('.php');

                if (class_exists($fullClassName)) {
                    (new $fullClassName)->run();
                } else {
                    $this->fail("Class {$fullClassName} does not exist.");
                }
            }
        }

        $this->info('Seeded email variants');
    }
}

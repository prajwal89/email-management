<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\text;

/**
 *  php artisan em:create-group
 */
class CreateReceivableGroupCommand extends Command
{
    protected $signature = 'em:create-group';

    protected $description = 'Command description';

    public function handle(): void
    {
        $data = [
            'name' => text(
                label: 'Group Name (without group suffix)',
                required: true,
                validate: ['name' => 'required|max:40']
            ),
            'description' => text(
                label: 'Description',
                default: 'Example Description'
                // required: true,
                // validate: ['name' => 'required|max:40']
            ),
        ];

        $this->createGroupClassFile($data);
    }

    public function createGroupClassFile(array $data): void
    {
        $className = str($data['name'])->slug()->studly() . 'Group';

        $emailHandlerStub = str(File::get(__DIR__ . '/../../stubs/groupable-class.stub'))
            ->replace('{class_name}', $className)
            ->replace('{description}', $data['description']);

        $receivablesPath = config('email-management.receivable_groups_path');

        $filePath = $receivablesPath . "/{$className}.php";

        if (!File::exists($receivablesPath)) {
            File::makeDirectory($receivablesPath, 0755, true);
        }

        if (File::exists($filePath)) {
            $this->error("Groupable Class file already exists: {$filePath}");

            return;
        }

        File::put($filePath, $emailHandlerStub);

        $this->info("Created Groupable Class file: {$filePath}.php");
    }
}

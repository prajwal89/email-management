<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\ReceivableGroup;

class ReceivableGroupService
{
    public static function destroy(ReceivableGroup $receivableGroup): bool
    {
        $filePath = config('email-management.receivable_groups_path') . "/{$receivableGroup->classname}.php";

        if (!File::exists($filePath)) {
            throw new Exception("Receivable Group Class file does not exists at: $filePath");
        }

        File::delete($filePath);

        return true;
    }
}

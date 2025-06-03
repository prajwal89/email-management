<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Support\Facades\DB;

class SentEmailService
{
    public static function destroy(EmailLog $sentEmail): bool
    {
        $sentEmail->load('emailVisits');

        DB::transaction(function () use ($sentEmail): void {
            $sentEmail->emailVisits()->delete();
            $sentEmail->delete();
        });

        return true;
    }
}

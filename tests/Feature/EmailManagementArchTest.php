<?php

declare(strict_types=1);

arch('MailHandlers have suffix of MailHandler')
    ->expect('Prajwal89\EmailManagement\MailHandlers\EmailCampaigns')
    ->expect('Prajwal89\EmailManagement\MailHandlers\EmailEvents')
    ->toHaveSuffix('Handler');

arch('MailHandlers should extend MailHandler')
    ->expect('Prajwal89\EmailManagement\MailHandlers\EmailCampaigns')
    ->expect('Prajwal89\EmailManagement\MailHandlers\EmailEvents')
    ->toExtend('Prajwal89\EmailManagement\MailHandlers\EmailHandlerBase');

arch('Groups have suffix of Group')
    ->expect('Prajwal89\EmailManagement\EmailReceivableGroups')
    ->toHaveSuffix('Group');

arch('Mail facade can only be used in Modules\\EmailManagement\\MailHandlers')
    ->expect('Illuminate\Support\Facades\Mail')
    ->toOnlyBeUsedIn('Prajwal89\EmailManagement\MailHandlers')
    ->ignoring('Prajwal89\EmailManagement\Concerns\SendsEmail')
    ->ignoring('Prajwal89\EmailManagement\MailHandlers\EmailHandlerBase');

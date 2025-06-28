<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Tests\Feature;

use Prajwal89\EmailManagement\BounceParser;
use Prajwal89\EmailManagement\Dtos\BounceDataDto;
use Prajwal89\EmailManagement\Tests\TestCase;

class BounceParserTest extends TestCase
{
    /**
     * @dataProvider bouncedEmailsProvider
     */
    public function test_it_can_parse_bounced_emails($filePath)
    {
        $content = file_get_contents($filePath);
        $parser = new BounceParser;
        $result = $parser->parse($content);

        $this->assertInstanceOf(BounceDataDto::class, $result);
        $this->assertNotNull($result->recipient);
    }

    /**
     * @dataProvider successEmailsProvider
     */
    public function test_it_can_parse_success_emails($filePath)
    {
        $content = file_get_contents($filePath);
        $parser = new BounceParser;
        $result = $parser->parse($content);

        $this->assertNull($result);
    }

    public static function bouncedEmailsProvider()
    {
        $bouncedEmailPaths = glob(__DIR__ . '/../../raw-emails/bounced/*.txt');

        return array_map(function ($filePath) {
            return [$filePath];
        }, $bouncedEmailPaths);
    }

    public static function successEmailsProvider()
    {
        $successEmailPaths = glob(__DIR__ . '/../../raw-emails/success/*.txt');

        return array_map(function ($filePath) {
            return [$filePath];
        }, $successEmailPaths);
    }
}

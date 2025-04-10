<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Support\Facades\URL;
use Symfony\Component\Mime\Header\Headers;

/**
 * This class is responsible for modifying email content
 */
class EmailContentModifiers
{
    /**
     * Inject image pixel that will make get call to our server
     *
     * e.g
     * <img border="0" width="1" alt="" height="1" src="http://127.0.0.1:8000/emails/pixel/Ea0TGWIeh6oVhDVhU0rX8bMXVFw2Q0rU" />
     */
    public static function injectTrackingPixel(string $html, string $hash): string
    {
        $url = route('emails.track.pixel', ['hash' => $hash]);

        // Append the tracking URL
        $trackingPixel = '<img border="0" width="1" alt="" height="1" src="' . $url . '" />';

        $lineBreak = str()->random(32);
        $html = str_replace("\n", $lineBreak, $html);

        if (preg_match('/^(.*<body[^>]*>)(.*)$/', $html, $matches)) {
            $html = $matches[1] . $trackingPixel . $matches[2];
        } else {
            $html .= $trackingPixel;
        }

        return str_replace($lineBreak, "\n", $html);
    }

    /**
     * Replaces all urls in email body except image urls
     * with the signed route for security
     *
     * e.g.
     * http://127.0.0.1:8000/emails/v/Ea0TGWIeh6oVhDVhU0rX8bMXVFw2Q0rU/aHR0cDovLzEyNy4wLjAuMTo4MDAw?signature=042ffc6fc0513ec6ae5d4ad6064136f7e3d975d7b41b5025df347d55d39b0861
     */
    public static function injectTrackingUrls(string $html, string $hash): string
    {
        return preg_replace_callback(
            '/<body[^>]*>(.*?)<\/body>/is', // Regex to match the body content
            function (array $matches) use ($hash): string {
                $bodyContent = $matches[1]; // Extract content inside <body>

                // Replace URLs in the body content
                $updatedBodyContent = preg_replace_callback(
                    '/https?:\/\/[^\s"]+/i', // Regex to match URLs
                    function (array $urlMatches) use ($hash, $bodyContent) {
                        $originalUrl = $urlMatches[0];

                        // Check if the URL is part of an <img> tag (this is a simple check for <img> tags, can be refined further)
                        if (preg_match('/<img[^>]+src=["\']' . preg_quote($originalUrl, '/') . '["\'][^>]*>/i', $bodyContent)) {
                            // Return the URL as is if it is part of an <img> tag
                            return $originalUrl;
                        }

                        return URL::signedRoute('emails.track.visit', [
                            'hash' => $hash,
                            'url' => urlencode($originalUrl),
                        ]);
                    },
                    $bodyContent
                );

                // Return the reconstructed body tag with updated content
                return '<body>' . $updatedBodyContent . '</body>';
            },
            $html
        );
    }

    public static function injectUnsubscribeLink(string $html, string $hash): string
    {
        $unsubscribeUrl = self::getUnsubscribeLink($hash);
        $unsubscribeLine = '<p style="text-align: center; font-size: 14px; color: #ddd;">
                                <a href="' . $unsubscribeUrl . '" style="color: #007bff; text-decoration: underline;">unsubscribe</a>
                            </p>';

        $lineBreak = str()->random(32);
        $html = str_replace("\n", $lineBreak, $html);

        // Append the unsubscribe line just before the closing </body> tag, or at the end of the HTML
        if (stripos($html, '</body>') !== false) {
            $html = str_ireplace('</body>', $unsubscribeLine . '</body>', $html);
        } else {
            $html .= $unsubscribeLine;
        }

        return str_replace($lineBreak, "\n", $html);
    }

    /**
     * Remove headers or it will get recorded in database
     */
    public static function removeHeaders(Headers &$headers): void
    {
        $headers->remove('X-Eventable-Type');
        $headers->remove('X-Eventable-Id');
        $headers->remove('X-Receivable-Type');
        $headers->remove('X-Receivable-Id');
    }

    public static function attachMailerHashHeader(Headers &$headers): string
    {
        // handles normal emails that are not sent from email hadler
        // is this required ?
        $hash = str()->random(32);
        $headers->addTextHeader('X-Mailer-Hash', (string) $hash);

        return $hash;
    }

    public static function addUnsubscribeHeader(Headers &$headers, string $hash): void
    {
        $unsubscribeUrl = self::getUnsubscribeLink($hash);
        $headers->addTextHeader('List-Unsubscribe', '<' . $unsubscribeUrl . '>');
        $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
    }

    public static function getUnsubscribeLink(string $hash): string
    {
        return URL::signedRoute('emails.unsubscribe', [
            'hash' => $hash,
        ]);
    }
}

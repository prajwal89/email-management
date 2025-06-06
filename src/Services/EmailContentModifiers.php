<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Support\Facades\URL;
use Symfony\Component\Mime\Email;

/**
 * This class is responsible for modifying email content
 */
class EmailContentModifiers
{
    public function __construct(public $email)
    {
        // dd($email->getallheaders());
        // $headersManager = new HeadersManager($email);
        // dd($headersManager);
    }

    /**
     * Replaces all urls in email body except image urls
     * with the signed route for security
     *
     * e.g.
     * http://127.0.0.1:8000/emails/v/Ea0TGWIeh6oVhDVhU0rX8bMXVFw2Q0rU/aHR0cDovLzEyNy4wLjAuMTo4MDAw?signature=042ffc6fc0513ec6ae5d4ad6064136f7e3d975d7b41b5025df347d55d39b0861
     */
    public function injectTrackingUrls()
    {
        $html = preg_replace_callback(
            pattern: '/<body[^>]*>(.*?)<\/body>/is', // Regex to match the body content
            callback: function (array $matches): string {
                $bodyContent = $matches[1]; // Extract content inside <body>

                // Replace URLs in the body content
                $updatedBodyContent = preg_replace_callback(
                    pattern: '/https?:\/\/[^\s"]+/i', // Regex to match URLs
                    callback: function (array $urlMatches) use ($bodyContent) {
                        $originalUrl = $urlMatches[0];

                        // Check if the URL is part of an <img> tag (this is a simple check for <img> tags, can be refined further)
                        if (preg_match(
                            pattern: '/<img[^>]+src=["\']' . preg_quote($originalUrl, '/') . '["\'][^>]*>/i',
                            subject: $bodyContent
                        )) {
                            // Return the URL as is if it is part of an <img> tag
                            return $originalUrl;
                        }

                        return URL::signedRoute('emails.redirect', [
                            'message_id' => 'test',
                            'url' => urlencode($originalUrl),
                        ]);
                    },
                    subject: $bodyContent
                );

                // Return the reconstructed body tag with updated content
                return '<body>' . $updatedBodyContent . '</body>';
            },
            subject: $this->email->render()
        );

        $this->email->html($html);

        return $this;
    }

    /**
     * Inject image pixel that will make get call to our server
     *
     * e.g
     * <img border="0" width="1" alt="" height="1" src="http://127.0.0.1:8000/emails/pixel/Ea0TGWIeh6oVhDVhU0rX8bMXVFw2Q0rU" />
     */
    public function injectTrackingPixel()
    {
        $url = route('emails.pixel', ['message_id' => 'test']);

        // Append the tracking URL
        $trackingPixel = '<img border="0" width="1" alt="" height="1" src="' . $url . '" />';

        $lineBreak = str()->random(32);
        $html = str_replace("\n", $lineBreak, $this->email->render());

        if (preg_match('/^(.*<body[^>]*>)(.*)$/', $html, $matches)) {
            $html = $matches[1] . $trackingPixel . $matches[2];
        } else {
            $html .= $trackingPixel;
        }

        $html = str_replace($lineBreak, "\n", $html);

        $this->email->html($html);

        return $this;
    }

    /**
     * create a footer
     */
    public function injectUnsubscribeLink(): self
    {
        $unsubscribeUrl = URL::signedRoute('emails.unsubscribe', [
            'hash' => 'test',
        ]);

        $unsubscribeLine = '<p style="text-align: center; font-size: 14px; color: #ddd;">
                                <a href="' . $unsubscribeUrl . '" style="color: #007bff; text-decoration: underline;">unsubscribe</a>
                            </p>';

        $lineBreak = str()->random(32);
        $html = str_replace("\n", $lineBreak, $this->email->render());

        // Append the unsubscribe line just before the closing </body> tag, or at the end of the HTML
        if (stripos($html, '</body>') !== false) {
            $html = str_ireplace('</body>', $unsubscribeLine . '</body>', $html);
        } else {
            $html .= $unsubscribeLine;
        }

        $html = str_replace($lineBreak, "\n", $html);

        $this->email->html($html);

        return $this;
    }
}

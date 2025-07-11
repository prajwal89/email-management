<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\URL;

/**
 * This class is responsible for modifying email content
 */
class EmailContentModifiers
{
    public function __construct(
        public Mailable $email,
        public string $messageId
    ) {}

    /**
     * Replaces all urls in email body except image urls
     * with the signed route for security
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
                            'message_id' => $this->messageId,
                            'url' => $originalUrl,
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
        $url = route('emails.pixel', ['message_id' => $this->messageId]);

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
            'message_id' => $this->messageId,
        ]);

        $unsubscribeLine = '
<div style="background: transparent; text-align: center; font-size: 14px; color: #999; margin-top: 30px;">
    <p style="margin: 0;">
        If you’d prefer not to receive these emails, you can 
        <a href="' . $unsubscribeUrl . '" style="color: #007bff; text-decoration: underline;">unsubscribe here</a>.
    </p>
</div>
';

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

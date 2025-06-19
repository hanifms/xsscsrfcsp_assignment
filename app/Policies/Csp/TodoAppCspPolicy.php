<?php

namespace App\Policies\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Policies\Policy;
use Spatie\Csp\Scheme;

class TodoAppCspPolicy extends Policy
{
    public function configure()
    {
        $this
            // Base URI restriction
            ->addDirective(Directive::BASE, 'self')

            // Default sources restriction - Allow all for debugging
            ->addDirective(Directive::DEFAULT, ['self', 'unsafe-inline', 'unsafe-eval', '*'])

            // Connections - Allow all for debugging
            ->addDirective(Directive::CONNECT, ['self', '*'])

            // Form submissions only to our domain
            ->addDirective(Directive::FORM_ACTION, 'self')

            // Images from anywhere
            ->addDirective(Directive::IMG, ['self', 'data:', '*'])

            // Media from our domain and data URLs
            ->addDirective(Directive::MEDIA, ['self', 'data:', '*'])

            // Disable plugins like Flash
            ->addDirective(Directive::OBJECT, 'none')

            // Scripts - Allow all for debugging
            ->addDirective(Directive::SCRIPT, ['self', 'unsafe-inline', 'unsafe-eval', '*'])

            // Allow fonts from anywhere
            ->addDirective(Directive::FONT, [
                'self',
                'data:',
                '*'
            ])

            // Style sources - Allow all for debugging
            ->addDirective(Directive::STYLE, [
                'self',
                'unsafe-inline',
                'data:',
                '*'
            ])

            // Add nonce support for inline scripts if needed
            ->addNonceForDirective(Directive::SCRIPT);
    }
}

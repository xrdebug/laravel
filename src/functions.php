<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\xrDebug\Laravel {
    function preHtml(string $content): string
    {
        return '<pre>'
            . htmlspecialchars($content)
            . '</pre>';
    }
}

namespace {
    use Chevere\xrDebug\Laravel\Interfaces\XrInspectorInterface;

    if (! function_exists('xrLaravel')) {
        function xrLaravel(): XrInspectorInterface
        {
            return app(XrInspectorInterface::class);
        }
    }
}

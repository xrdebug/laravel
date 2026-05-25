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

namespace Chevere\xrDebug\Laravel\Watchers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use function Chevere\xrDebug\Laravel\preHtml;

class DeprecatedNoticeWatcher extends Watcher
{
    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_deprecated_notices', false);
        Event::listen(
            MessageLogged::class,
            function (MessageLogged $message): void {
                if (! $this->isEnabled() || ! $this->isDeprecationMessage($message)) {
                    return;
                }
                xrr(
                    preHtml((string) $message->message),
                    t: 'Deprecated',
                    e: '⚠️ Notice'
                );
            }
        );
    }

    public function isDeprecationMessage(MessageLogged $message): bool
    {
        return Str::contains(
            (string) $message->message,
            ['deprecated', 'Deprecated', '[\ReturnTypeWillChange]']
        );
    }
}

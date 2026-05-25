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
use Throwable;
use function Chevere\xrDebug\PHP\throwableHandler;

class ExceptionWatcher extends Watcher
{
    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_exceptions', true);
        Event::listen(
            MessageLogged::class,
            function (MessageLogged $message): void {
                if (! $this->isEnabled() || ! $this->concernsException($message)) {
                    return;
                }
                throwableHandler($message->context['exception']);
            }
        );
    }

    public function concernsException(MessageLogged $message): bool
    {
        return isset($message->context['exception'])
            && $message->context['exception'] instanceof Throwable;
    }
}

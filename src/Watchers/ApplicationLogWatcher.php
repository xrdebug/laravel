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
use function Chevere\xrDebug\Laravel\preHtml;

class ApplicationLogWatcher extends Watcher
{
    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_logs', true);
        Event::listen(
            MessageLogged::class,
            function (MessageLogged $message): void {
                if (! $this->shouldLog($message)) {
                    return;
                }
                $emote = match ($message->level) {
                    'error', 'critical', 'alert', 'emergency' => '🔴',
                    'warning' => '🟠',
                    default => '🔵',
                };
                $messageLevel = ucfirst($message->level);
                xrr(
                    preHtml((string) $message->message),
                    t: 'Log',
                    e: "{$emote} {$messageLevel}"
                );
            }
        );
    }

    private function shouldLog(MessageLogged $message): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }
        if ($message->message === null) {
            return false;
        }
        if ((new ExceptionWatcher())->concernsException($message)) {
            return false;
        }
        if ((new MailWatcher())->concernsLoggedMail($message)) {
            return false;
        }
        if ((new DeprecatedNoticeWatcher())->isDeprecationMessage($message)) {
            return false;
        }

        return true;
    }
}

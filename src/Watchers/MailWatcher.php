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
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use function Chevere\xrDebug\Laravel\preHtml;

class MailWatcher extends Watcher
{
    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_mails', true);
        if ($this->supportsMessageSendingEvent()) {
            Event::listen(
                MessageSending::class,
                function (MessageSending $event): void {
                    if (! $this->isEnabled()) {
                        return;
                    }
                    $body = $event->message->getHtmlBody() ?? $event->message->getTextBody() ?? '';
                    xrr(
                        (string) $body,
                        t: 'Mail',
                        e: '✉︎ Sending',
                    );
                }
            );
        } else {
            Event::listen(
                MessageLogged::class,
                function (MessageLogged $message): void {
                    if (! $this->isEnabled()) {
                        return;
                    }
                    if (! $this->concernsLoggedMail($message)) {
                        return;
                    }
                    xrr(
                        preHtml((string) $message->message),
                        t: 'Mail',
                        e: '✉︎ Logged',
                    );
                }
            );
        }
    }

    public function concernsLoggedMail(MessageLogged $message): bool
    {
        return Str::contains((string) $message->message, 'Message-ID')
            && Str::contains((string) $message->message, 'To:');
    }

    protected function supportsMessageSendingEvent(): bool
    {
        return version_compare(app()->version(), '11.0.0', '>=');
    }
}

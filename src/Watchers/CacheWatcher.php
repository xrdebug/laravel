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

use Chevere\xrDebug\Laravel\Payloads\TableHtml;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Event;

class CacheWatcher extends Watcher
{
    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_cache', false);
        Event::listen(
            CacheHit::class,
            function (CacheHit $event): void {
                if (! $this->isEnabled()) {
                    return;
                }
                xrr(
                    (string) new TableHtml(
                        [
                            'Key' => $event->key,
                            'Tags' => $event->tags,
                            'Value' => $event->value,
                        ]
                    ),
                    t: 'Cache',
                    e: '🆗 Hit'
                );
            }
        );
        Event::listen(
            CacheMissed::class,
            function (CacheMissed $event): void {
                if (! $this->isEnabled()) {
                    return;
                }
                xrr(
                    (string) new TableHtml(
                        [
                            'Key' => $event->key,
                            'Tags' => $event->tags,
                        ]
                    ),
                    t: 'Cache',
                    e: '❌ Missed'
                );
            }
        );
        Event::listen(
            KeyWritten::class,
            function (KeyWritten $event): void {
                if (! $this->isEnabled()) {
                    return;
                }
                xrr(
                    (string) new TableHtml(
                        [
                            'Key' => $event->key,
                            'Tags' => $event->tags,
                            'Value' => $event->value,
                            'Expiration' => $event->seconds,
                        ]
                    ),
                    t: 'Cache',
                    e: '💾 Written'
                );
            }
        );
        Event::listen(
            KeyForgotten::class,
            function (KeyForgotten $event): void {
                if (! $this->isEnabled()) {
                    return;
                }
                xrr(
                    (string) new TableHtml(
                        [
                            'Key' => $event->key,
                            'Tags' => $event->tags,
                        ]
                    ),
                    t: 'Cache',
                    e: '🗑️ Forgotten'
                );
            }
        );
    }
}

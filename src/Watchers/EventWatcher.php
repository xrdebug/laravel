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

use Illuminate\Support\Facades\Event;
use function Chevere\xrDebug\Laravel\preHtml;

class EventWatcher extends Watcher
{
    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_events', false);
        Event::listen(
            '*',
            function (string $eventName, array $arguments): void {
                if (! $this->isEnabled()) {
                    return;
                }
                xrr(
                    preHtml($eventName),
                    t: 'Event'
                );
            }
        );
    }
}

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

use Chevere\xrDebug\Laravel\Payloads\RequestHandledPayload;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;

class RequestHandledWatcher extends Watcher
{
    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_requests', false);
        Event::listen(
            RequestHandled::class,
            function (RequestHandled $requestHandled): void {
                if (! $this->isEnabled()) {
                    return;
                }
                xrr(
                    (new RequestHandledPayload($requestHandled))->getHtml(),
                    t: 'Request Handled',
                );
            }
        );
    }
}

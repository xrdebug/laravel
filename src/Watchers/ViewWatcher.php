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
use Illuminate\View\View;
use function Chevere\xrDebug\Laravel\preHtml;

class ViewWatcher extends Watcher
{
    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_views', false);
        Event::listen(
            'composing:*',
            function (string $event, array $data): void {
                if (! $this->isEnabled()) {
                    return;
                }
                /** @var View $view */
                $view = $data[0];
                xrr(
                    preHtml($view->getName()),
                    t: 'View'
                );
            }
        );
    }
}

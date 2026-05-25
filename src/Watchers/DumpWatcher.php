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

use Symfony\Component\VarDumper\VarDumper;

class DumpWatcher extends Watcher
{
    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_dumps', true);
        if (! $this->isEnabled()) {
            return;
        }
        VarDumper::setHandler(function (mixed $var): void {
            xr($var);
        });
    }
}

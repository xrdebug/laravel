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

use Illuminate\Database\Events\QueryExecuted;

class SlowQueryWatcher extends ConditionalQueryWatcher
{
    private float $minimumTimeInMs = 500;

    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_slow_queries', false);
        /** @var float $threshold */
        $threshold = config('xr.slow_query_threshold_ms', 500);
        $this->minimumTimeInMs = $threshold;
        $this->setConditionalCallback(
            function (QueryExecuted $query): bool {
                return $query->time >= $this->minimumTimeInMs;
            }
        );
    }

    public function setMinimumTimeInMilliseconds(float $milliseconds): static
    {
        $this->minimumTimeInMs = $milliseconds;

        return $this;
    }

    protected function getEmote(): string
    {
        return '⧗ Slow';
    }
}

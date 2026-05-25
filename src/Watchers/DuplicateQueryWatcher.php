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

use DateTimeInterface;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class DuplicateQueryWatcher extends QueryWatcher
{
    /**
     * @var list<string>
     */
    private array $seenQueries = [];

    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_duplicate_queries', false);
        Event::listen(
            QueryExecuted::class,
            function (QueryExecuted $query): void {
                if (! $this->isEnabled()) {
                    return;
                }
                $sql = Str::replaceArray('?', $this->cleanupBindings($query->bindings), $query->sql);
                $isDuplicate = in_array($sql, $this->seenQueries, true);
                $this->seenQueries[] = $sql;
                if (! $isDuplicate) {
                    return;
                }
                $this->sendQuery($query, 'Query', '🔁 Duplicate');
            }
        );
    }

    public function enable(): static
    {
        if (app()->bound('db')) {
            collect(DB::getConnections())->each(
                function (mixed $connection): void {
                    /** @phpstan-ignore-next-line */
                    $connection->enableQueryLog();
                }
            );
        }
        parent::enable();

        return $this;
    }

    public function disable(): static
    {
        DB::disableQueryLog();
        parent::disable();

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getSeenQueries(): array
    {
        return $this->seenQueries;
    }

    /**
     * @param mixed[] $bindings
     * @return string[]
     */
    private function cleanupBindings(array $bindings): array
    {
        return array_map(
            function (mixed $binding): string {
                if ($binding instanceof DateTimeInterface) {
                    return $binding->format('Y-m-d H:i:s');
                }

                return is_scalar($binding)
                    ? (string) $binding
                    : '';
            },
            $bindings
        );
    }
}

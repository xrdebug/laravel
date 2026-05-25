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

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;

abstract class ConditionalQueryWatcher extends QueryWatcher
{
    protected ?Closure $conditionalCallback = null;

    final public function __construct()
    {
    }

    public static function buildWatcherForName(Closure $condition, string $name): self
    {
        $watcher = new static();
        $watcher->setConditionalCallback($condition);

        return app()->instance(static::abstractName($name), $watcher);
    }

    public static function abstractName(string $name): string
    {
        return static::class . ':' . $name;
    }

    public function setConditionalCallback(Closure $callback): void
    {
        $this->conditionalCallback = $callback;
        $this->listen();
    }

    protected function listen(): void
    {
        Event::listen(
            QueryExecuted::class,
            function (QueryExecuted $query): void {
                if (! $this->isEnabled() || $this->conditionalCallback === null) {
                    return;
                }
                if (($this->conditionalCallback)($query)) {
                    $this->sendQuery(
                        $query,
                        $this->getTopic(),
                        $this->getEmote()
                    );
                }
            }
        );
    }

    protected function getTopic(): string
    {
        return 'Query';
    }

    abstract protected function getEmote(): string;
}

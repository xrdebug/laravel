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

namespace Chevere\xrDebug\Laravel;

use Chevere\xrDebug\Laravel\Interfaces\XrInspectorInterface;
use Chevere\xrDebug\Laravel\Payloads\TableHtml;
use Chevere\xrDebug\Laravel\Watchers\CacheWatcher;
use Chevere\xrDebug\Laravel\Watchers\ConditionalQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\DeleteQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\DuplicateQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\EventWatcher;
use Chevere\xrDebug\Laravel\Watchers\ApplicationLogWatcher;
use Chevere\xrDebug\Laravel\Watchers\ExceptionWatcher;
use Chevere\xrDebug\Laravel\Watchers\HttpClientRequestWatcher;
use Chevere\xrDebug\Laravel\Watchers\InsertQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\JobWatcher;
use Chevere\xrDebug\Laravel\Watchers\MailWatcher;
use Chevere\xrDebug\Laravel\Watchers\QueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\RequestHandledWatcher;
use Chevere\xrDebug\Laravel\Watchers\SelectQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\SlowQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\UpdateQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\ViewWatcher;
use Chevere\xrDebug\Laravel\Watchers\Watcher;
use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Collection;

class XrInspector implements XrInspectorInterface
{
    public function showMails(?callable $callable = null): XrInspectorInterface
    {
        return $this->handleWatcherCallable(
            app(MailWatcher::class),
            $callable
        );
    }

    public function stopShowingMails(): XrInspectorInterface
    {
        app(MailWatcher::class)->disable();

        return $this;
    }

    public function showEvents(?callable $callable = null): XrInspectorInterface
    {
       return $this->handleWatcherCallable(
           app(EventWatcher::class),
           $callable
       );
    }

    public function stopShowingEvents(): XrInspectorInterface
    {
        app(EventWatcher::class)->disable();

        return $this;
    }

    public function showLogs(): XrInspectorInterface
    {
        app(ApplicationLogWatcher::class)->enable();

        return $this;
    }

    public function stopShowingLogs(): XrInspectorInterface
    {
        app(ApplicationLogWatcher::class)->disable();

        return $this;
    }

    public function showExceptions(): XrInspectorInterface
    {
        app(ExceptionWatcher::class)->enable();

        return $this;
    }

    public function stopShowingExceptions(): XrInspectorInterface
    {
        app(ExceptionWatcher::class)->disable();

        return $this;
    }

    public function showJobs(?callable $callable = null): XrInspectorInterface
    {
        return $this->handleWatcherCallable(
            app(JobWatcher::class),
            $callable
        );
    }

    public function stopShowingJobs(): XrInspectorInterface
    {
        app(JobWatcher::class)->disable();

        return $this;
    }

    public function showCache(?callable $callable = null): XrInspectorInterface
    {
        return $this->handleWatcherCallable(
            app(CacheWatcher::class),
            $callable
        );
    }

    public function stopShowingCache(): XrInspectorInterface
    {
        app(CacheWatcher::class)->disable();

        return $this;
    }

    public function showViews(?callable $callable = null): XrInspectorInterface
    {
        return $this->handleWatcherCallable(
            app(ViewWatcher::class),
            $callable
        );
    }

    public function stopShowingViews(): XrInspectorInterface
    {
        app(ViewWatcher::class)->disable();

        return $this;
    }

    public function showQueries(?callable $callable = null): XrInspectorInterface
    {
        return $this->handleWatcherCallable(
            app(QueryWatcher::class),
            $callable
        );
    }

    public function countQueries(callable $callable): XrInspectorInterface
    {
        /** @var QueryWatcher $watcher */
        $watcher = app(QueryWatcher::class);
        $watcher->keepExecutedQueries();
        if (! $watcher->isEnabled()) {
            $watcher->doNotSendIndividualQueries();
        }
        $this->handleWatcherCallable($watcher, $callable);
        $executedQueryStatistics = collect($watcher->getExecutedQueries())
            ->pipe(function (Collection $queries) {
                return [
                    'Count' => $queries->count(),
                    'Total time' => $queries->sum(
                        function (QueryExecuted $query) {
                            return $query->time;
                        }
                    ),
                ];
            });
        $executedQueryStatistics['Total time'] .= ' ms';
        $watcher
            ->stopKeepingAndClearExecutedQueries()
            ->sendIndividualQueries();
        xrr(
            (string) new TableHtml($executedQueryStatistics),
            t: 'Query statistics',
        );

        return $this;
    }

    public function stopShowingQueries(): XrInspectorInterface
    {
        app(QueryWatcher::class)->disable();

        return $this;
    }

    public function showSlowQueries(int $milliseconds = 500, ?callable $callable = null): XrInspectorInterface
    {
        return $this->handleWatcherCallable(
            app(SlowQueryWatcher::class)->setMinimumTimeInMilliseconds($milliseconds),
            $callable
        );
    }

    public function stopShowingSlowQueries(): XrInspectorInterface
    {
        app(SlowQueryWatcher::class)->disable();

        return $this;
    }

    public function showDuplicateQueries(?callable $callable = null): XrInspectorInterface
    {
        return $this->handleWatcherCallable(
            app(DuplicateQueryWatcher::class),
            $callable
        );
    }

    public function stopShowingDuplicateQueries(): XrInspectorInterface
    {
        app(DuplicateQueryWatcher::class)->disable();

        return $this;
    }

    public function showConditionalQueries(
        Closure $condition,
        ?callable $callable = null,
        string $name = 'default'
    ): XrInspectorInterface {
        return $this->handleWatcherCallable(
            ConditionalQueryWatcher::buildWatcherForName($condition, $name),
            $callable
        );
    }

    public function stopShowingConditionalQueries(string $name = 'default'): XrInspectorInterface
    {
        /** @var ConditionalQueryWatcher $watcher */
        $watcher = app(ConditionalQueryWatcher::abstractName($name));
        $watcher->disable();

        return $this;
    }

    public function showUpdateQueries(?callable $callable = null): XrInspectorInterface
    {
        return $this->handleWatcherCallable(
            app(UpdateQueryWatcher::class),
            $callable
        );
    }

    public function stopShowingUpdateQueries(): XrInspectorInterface
    {
        app(UpdateQueryWatcher::class)->disable();

        return $this;
    }

    public function showDeleteQueries(?callable $callable = null): XrInspectorInterface
    {
        return $this->handleWatcherCallable(
            app(DeleteQueryWatcher::class),
            $callable
        );
    }

    public function stopShowingDeleteQueries(): XrInspectorInterface
    {
        app(DeleteQueryWatcher::class)->disable();

        return $this;
    }

    public function showInsertQueries(?callable $callable = null): XrInspectorInterface
    {
        return $this->handleWatcherCallable(
            app(InsertQueryWatcher::class),
            $callable
        );
    }

    public function stopShowingInsertQueries(): XrInspectorInterface
    {
        app(InsertQueryWatcher::class)->disable();

        return $this;
    }

    public function showSelectQueries(?callable $callable = null): XrInspectorInterface
    {
        return $this->handleWatcherCallable(
            app(SelectQueryWatcher::class),
            $callable
        );
    }

    public function stopShowingSelectQueries(): XrInspectorInterface
    {
        app(SelectQueryWatcher::class)->disable();

        return $this;
    }

    public function showRequests(?callable $callable = null): XrInspectorInterface
    {
        return $this->handleWatcherCallable(
            app(RequestHandledWatcher::class),
            $callable
        );
    }

    public function stopShowingRequests(): XrInspectorInterface
    {
        app(RequestHandledWatcher::class)->disable();

        return $this;
    }

    public function showHttpClientRequests(?callable $callable = null): XrInspectorInterface
    {
        if (! HttpClientRequestWatcher::supportedByLaravelVersion()) {
            xrr(
                'Http logging is not available in your Laravel version',
                t: 'Http [Request]',
                e: '🔴'
            );

            return $this;
        }

        return $this->handleWatcherCallable(
            app(HttpClientRequestWatcher::class),
            $callable
        );
    }

    public function stopShowingHttpClientRequests(): XrInspectorInterface
    {
        app(HttpClientRequestWatcher::class)->disable();

        return $this;
    }

    protected function handleWatcherCallable(Watcher $watcher, ?callable $callable = null): self
    {
        $wasEnabled = $watcher->isEnabled();
        $watcher->enable();
        if ($callable) {
            $callable();
            if (! $wasEnabled) {
                $watcher->disable();
            }
        }

        return $this;
    }
}

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

use Chevere\xrDebug\Laravel\Payloads\ExecutedQueryPayload;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use function Chevere\xrDebug\Laravel\preHtml;

class QueryWatcher extends Watcher
{
    /**
     * @var list<QueryExecuted>
     */
    protected array $executedQueries = [];

    protected bool $keepExecutedQueries = false;

    protected bool $sendIndividualQueries = true;

    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_queries', false);
        Event::listen(
            QueryExecuted::class,
            function (QueryExecuted $query): void {
                if (! $this->isEnabled()) {
                    return;
                }
                if ($this->keepExecutedQueries) {
                    $this->executedQueries[] = $query;
                }

                if (! $this->sendIndividualQueries) {
                    return;
                }
                $this->sendQuery($query);
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

    public function keepExecutedQueries(): static
    {
        $this->keepExecutedQueries = true;

        return $this;
    }

    /**
     * @return list<QueryExecuted>
     */
    public function getExecutedQueries(): array
    {
        return $this->executedQueries;
    }

    public function sendIndividualQueries(): static
    {
        $this->sendIndividualQueries = true;

        return $this;
    }

    public function doNotSendIndividualQueries(): static
    {
        $this->sendIndividualQueries = false;

        return $this;
    }

    public function stopKeepingAndClearExecutedQueries(): static
    {
        $this->keepExecutedQueries = false;
        $this->executedQueries = [];

        return $this;
    }

    public function disable(): static
    {
        DB::disableQueryLog();
        parent::disable();

        return $this;
    }

    protected function sendQuery(
        QueryExecuted $query,
        string $topic = 'Query',
        string $emote = ''
    ): void {
        $content = (new ExecutedQueryPayload($query))->getContent();
        $body = preHtml((string) $content['sql']);
        if (isset($content['time'])) {
            $body .= '<p>'
                . htmlspecialchars((string) $content['connection_name'])
                . ' | '
                . $content['time']
                . 'ms</p>';
        }
        xrr($body, t: $topic, e: $emote);
    }
}

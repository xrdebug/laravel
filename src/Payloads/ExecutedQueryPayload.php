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

namespace Chevere\xrDebug\Laravel\Payloads;

use Illuminate\Database\Events\QueryExecuted;

class ExecutedQueryPayload
{
    public function __construct(
        private readonly QueryExecuted $query
    ) {
    }

    /**
     * @return array{sql: string, bindings?: mixed[]}|array{sql: string, connection_name: string, time: float}
     */
    public function getContent(): array
    {
        $grammar = $this->query->connection->getQueryGrammar();
        $properties = $this->supportsSubstituteBindingsIntoRawSql($grammar)
            ? [
                'sql' => $grammar->substituteBindingsIntoRawSql(
                    $this->query->sql,
                    $this->query->connection->prepareBindings($this->query->bindings)
                ),
            ]
            : [
                'sql' => $this->query->sql,
                'bindings' => $this->query->bindings,
            ];
        if ($this->query->time !== null) {
            $properties = array_merge($properties, [
                'connection_name' => $this->query->connectionName,
                'time' => $this->query->time,
            ]);
        }

        return $properties;
    }

    private function supportsSubstituteBindingsIntoRawSql(mixed $grammar): bool
    {
        return is_object($grammar)
            && method_exists($grammar, 'substituteBindingsIntoRawSql');
    }
}

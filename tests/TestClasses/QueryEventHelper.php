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

namespace Chevere\Tests\TestClasses;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

trait QueryEventHelper
{
    private function makeQueryEvent(
        string $sql = 'select 1',
        array $bindings = [],
        float $time = 10.0
    ): QueryExecuted {
        return new QueryExecuted($sql, $bindings, $time, DB::connection());
    }

    private function dispatchQueryEvent(
        string $sql = 'select 1',
        array $bindings = [],
        float $time = 10.0
    ): void {
        event($this->makeQueryEvent($sql, $bindings, $time));
    }
}

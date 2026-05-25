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

class DeleteQueryWatcher extends ConditionalQueryWatcher
{
    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_delete_queries', false);
        $this->setConditionalCallback(
            function (QueryExecuted $query): bool {
                return str_starts_with(strtolower($query->sql), 'delete');
            }
        );
    }

    protected function getEmote(): string
    {
        return '🗑️ Delete';
    }
}

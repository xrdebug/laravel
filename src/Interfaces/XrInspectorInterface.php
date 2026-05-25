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

namespace Chevere\xrDebug\Laravel\Interfaces;

use Closure;

interface XrInspectorInterface
{
    public function showMails(?callable $callable = null): self;

    public function stopShowingMails(): self;

    public function showEvents(?callable $callable = null): self;

    public function stopShowingEvents(): self;

    public function showLogs(): self;

    public function stopShowingLogs(): self;

    public function showExceptions(): self;

    public function stopShowingExceptions(): self;

    public function showJobs(?callable $callable = null): self;

    public function stopShowingJobs(): self;

    public function showCache(?callable $callable = null): self;

    public function stopShowingCache(): self;

    public function showViews(?callable $callable = null): self;

    public function stopShowingViews(): self;

    public function showQueries(?callable $callable = null): self;

    public function countQueries(callable $callable): self;

    public function stopShowingQueries(): self;

    public function showSlowQueries(int $milliseconds = 500, ?callable $callable = null): self;

    public function stopShowingSlowQueries(): self;

    public function showDuplicateQueries(?callable $callable = null): self;

    public function stopShowingDuplicateQueries(): self;

    public function showConditionalQueries(Closure $condition, ?callable $callable = null, string $name = 'default'): self;

    public function stopShowingConditionalQueries(string $name = 'default'): self;

    public function showUpdateQueries(?callable $callable = null): self;

    public function stopShowingUpdateQueries(): self;

    public function showDeleteQueries(?callable $callable = null): self;

    public function stopShowingDeleteQueries(): self;

    public function showInsertQueries(?callable $callable = null): self;

    public function stopShowingInsertQueries(): self;

    public function showSelectQueries(?callable $callable = null): self;

    public function stopShowingSelectQueries(): self;

    public function showRequests(?callable $callable = null): self;

    public function stopShowingRequests(): self;

    public function showHttpClientRequests(?callable $callable = null): self;

    public function stopShowingHttpClientRequests(): self;
}

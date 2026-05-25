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

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Event;
use function Chevere\xrDebug\Laravel\preHtml;

class JobWatcher extends Watcher
{
    public function register(): void
    {
        $this->isEnabled = (bool) config('xr.show_jobs', false);
        Event::listen(
            JobQueued::class,
            function (JobQueued $event): void {
                if (! $this->isEnabled()) {
                    return;
                }
                $job = is_object($event->job)
                    ? get_class($event->job)
                    : (string) $event->job;
                xrr(
                    preHtml($job),
                    t: 'Job',
                    e: '∆ Queued'
                );
            }
        );
        Event::listen(
            JobProcessing::class,
            function (JobProcessing $event): void {
                if (! $this->isEnabled()) {
                    return;
                }
                xrr(
                    preHtml($event->job->getName()),
                    t: 'Job',
                    e: '⏲ Processing'
                );
            }
        );
        Event::listen(
            JobProcessed::class,
            function (JobProcessed $event): void {
                if (! $this->isEnabled()) {
                    return;
                }
                xrr(
                    preHtml($event->job->getName()),
                    t: 'Job',
                    e: '✔︎ Processed'
                );
            }
        );
        Event::listen(
            JobFailed::class,
            function (JobFailed $event): void {
                if (! $this->isEnabled()) {
                    return;
                }
                xrr(
                    preHtml($event->job->getName()),
                    t: 'Job',
                    e: '✕ Failed'
                );
            }
        );
    }
}

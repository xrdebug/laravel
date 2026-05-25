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

namespace Chevere\Tests\Watchers;

use Chevere\Tests\TestCase;
use Chevere\xrDebug\Laravel\Watchers\JobWatcher;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

final class JobWatcherTest extends TestCase
{
    public function testDefaultsToDisabled(): void
    {
        $watcher = new JobWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testJobQueuedSendsMessage(): void
    {
        event(new JobQueued('sync', null, null, 'MyJob', '{}', null));
        $this->assertSentCount(1);
    }

    public function testJobQueuedTopic(): void
    {
        event(new JobQueued('sync', null, null, 'MyJob', '{}', null));
        $this->assertSame('Job', $this->lastMessage()->topic());
    }

    public function testJobQueuedBodyContainsJobName(): void
    {
        event(new JobQueued('sync', null, null, 'MyJobClass', '{}', null));
        $this->assertBodyContains('MyJobClass');
    }

    public function testJobQueuedWithObjectJob(): void
    {
        $jobObject = new class() {
            public function __toString(): string
            {
            return 'ObjectJob';
            }
        };
        event(new JobQueued('sync', null, null, $jobObject, '{}', null));
        $this->assertBodyContains(get_class($jobObject));
    }

    public function testJobProcessingSendsMessage(): void
    {
        event(new JobProcessing('sync', $this->makeJobMock('TestJob')));
        $this->assertSentCount(1);
    }

    public function testJobProcessingTopic(): void
    {
        event(new JobProcessing('sync', $this->makeJobMock('TestJob')));
        $this->assertSame('Job', $this->lastMessage()->topic());
    }

    public function testJobProcessedSendsMessage(): void
    {
        event(new JobProcessed('sync', $this->makeJobMock('TestJob')));
        $this->assertSentCount(1);
    }

    public function testJobProcessedTopic(): void
    {
        event(new JobProcessed('sync', $this->makeJobMock('TestJob')));
        $this->assertSame('Job', $this->lastMessage()->topic());
    }

    public function testJobFailedSendsMessage(): void
    {
        event(new JobFailed('sync', $this->makeJobMock('TestJob'), new RuntimeException()));
        $this->assertSentCount(1);
    }

    public function testJobFailedTopic(): void
    {
        event(new JobFailed('sync', $this->makeJobMock('TestJob'), new RuntimeException()));
        $this->assertSame('Job', $this->lastMessage()->topic());
    }

    public function testDisabledWatcherDoesNotSendOnQueued(): void
    {
        $this->disableWatcher(JobWatcher::class);
        event(new JobQueued('sync', null, null, 'MyJob', '{}', null));
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSendOnProcessing(): void
    {
        $this->disableWatcher(JobWatcher::class);
        event(new JobProcessing('sync', $this->makeJobMock('TestJob')));
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSendOnProcessed(): void
    {
        $this->disableWatcher(JobWatcher::class);
        event(new JobProcessed('sync', $this->makeJobMock('TestJob')));
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSendOnFailed(): void
    {
        $this->disableWatcher(JobWatcher::class);
        event(new JobFailed('sync', $this->makeJobMock('TestJob'), new RuntimeException()));
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_jobs', true);
    }

    private function makeJobMock(string $name): Job&MockObject
    {
        $mock = $this->createMock(Job::class);
        $mock->method('getName')->willReturn($name);

        return $mock;
    }
}

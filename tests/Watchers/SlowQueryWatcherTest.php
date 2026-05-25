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
use Chevere\Tests\TestClasses\QueryEventHelper;
use Chevere\xrDebug\Laravel\Watchers\SlowQueryWatcher;

final class SlowQueryWatcherTest extends TestCase
{
    use QueryEventHelper;

    public function testDefaultsToDisabled(): void
    {
        $watcher = new SlowQueryWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testFastQueryIsNotSent(): void
    {
        $this->dispatchQueryEvent('select 1', [], 100.0);
        $this->assertNoMessagesSent();
    }

    public function testSlowQueryIsSent(): void
    {
        $this->dispatchQueryEvent('select 1', [], 600.0);
        $this->assertSentCount(1);
    }

    public function testTopicIsSlowQuery(): void
    {
        $this->dispatchQueryEvent('select 1', [], 600.0);
        $this->assertSame('Query', $this->lastMessage()->topic());
    }

    public function testQueryAtExactThresholdIsSent(): void
    {
        $this->dispatchQueryEvent('select 1', [], 500.0);
        $this->assertSentCount(1);
    }

    public function testCustomThresholdViaConfig(): void
    {
        $this->app['config']->set('xr.slow_query_threshold_ms', 100);
        $watcher = new SlowQueryWatcher();
        $watcher->register();
        $this->app->instance(SlowQueryWatcher::class, $watcher);
        $this->dispatchQueryEvent('select 1', [], 150.0);
        $this->assertSentCount(1);
    }

    public function testSetMinimumTimeInMilliseconds(): void
    {
        /** @var SlowQueryWatcher $watcher */
        $watcher = $this->app->make(SlowQueryWatcher::class);
        $watcher->setMinimumTimeInMilliseconds(50.0);
        $this->dispatchQueryEvent('select 1', [], 60.0);
        $this->assertSentCount(1);
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(SlowQueryWatcher::class);
        $this->dispatchQueryEvent('select 1', [], 600.0);
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_slow_queries', true);
    }
}

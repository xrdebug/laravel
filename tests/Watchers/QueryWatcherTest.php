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
use Chevere\xrDebug\Laravel\Watchers\QueryWatcher;
use Illuminate\Support\Facades\DB;

final class QueryWatcherTest extends TestCase
{
    use QueryEventHelper;

    public function testDefaultsToDisabled(): void
    {
        $app = $this->createApplication();
        $app['config']->set('xr.show_queries', false);
        /** @var QueryWatcher $watcher */
        $watcher = $app->make(QueryWatcher::class);
        $watcher->register();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testDefaultsToEnabledWhenConfigTrue(): void
    {
        /** @var QueryWatcher $watcher */
        $watcher = $this->app->make(QueryWatcher::class);
        $this->assertTrue($watcher->isEnabled());
    }

    public function testSendsMessageOnQueryEvent(): void
    {
        $this->dispatchQueryEvent('select 1');
        $this->assertSentCount(1);
    }

    public function testTopicIsQuery(): void
    {
        $this->dispatchQueryEvent('select 1');
        $this->assertSame('Query', $this->lastMessage()->topic());
    }

    public function testBodyContainsSql(): void
    {
        $this->dispatchQueryEvent('select * from users');
        $this->assertBodyContains('select * from users');
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(QueryWatcher::class);
        $this->dispatchQueryEvent('select 1');
        $this->assertNoMessagesSent();
    }

    public function testKeepExecutedQueriesStoresQuery(): void
    {
        /** @var QueryWatcher $watcher */
        $watcher = $this->app->make(QueryWatcher::class);
        $watcher->keepExecutedQueries();
        $this->dispatchQueryEvent('select 1');
        $this->assertCount(1, $watcher->getExecutedQueries());
    }

    public function testDoNotSendIndividualQueriesSkipsSend(): void
    {
        /** @var QueryWatcher $watcher */
        $watcher = $this->app->make(QueryWatcher::class);
        $watcher->doNotSendIndividualQueries();
        $this->dispatchQueryEvent('select 1');
        $this->assertNoMessagesSent();
    }

    public function testSendIndividualQueriesResumesAfterDisabling(): void
    {
        /** @var QueryWatcher $watcher */
        $watcher = $this->app->make(QueryWatcher::class);
        $watcher->doNotSendIndividualQueries();
        $this->dispatchQueryEvent('select 1');
        $this->assertNoMessagesSent();
        $watcher->sendIndividualQueries();
        $this->dispatchQueryEvent('select 2');
        $this->assertSentCount(1);
    }

    public function testKeepAndClearExecutedQueries(): void
    {
        /** @var QueryWatcher $watcher */
        $watcher = $this->app->make(QueryWatcher::class);
        $watcher->keepExecutedQueries();
        $this->dispatchQueryEvent('select 1');
        $this->assertCount(1, $watcher->getExecutedQueries());
        $watcher->stopKeepingAndClearExecutedQueries();
        $this->assertCount(0, $watcher->getExecutedQueries());
    }

    public function testEnableStartsQueryLogOnConnections(): void
    {
        DB::connection(); // Resolve the connection so it appears in getConnections()
        /** @var QueryWatcher $watcher */
        $watcher = $this->app->make(QueryWatcher::class);
        $watcher->enable();
        $this->assertTrue(DB::connection()->logging());
    }

    public function testEnableWhenDbNotBound(): void
    {
        $this->app->offsetUnset('db');
        $watcher = new QueryWatcher();
        $watcher->enable();
        $this->assertTrue($watcher->isEnabled());
    }

    public function testDisableStopsQueryLog(): void
    {
        DB::connection()->enableQueryLog();
        $this->assertTrue(DB::connection()->logging());
        /** @var QueryWatcher $watcher */
        $watcher = $this->app->make(QueryWatcher::class);
        $watcher->disable();
        $this->assertFalse(DB::connection()->logging());
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_queries', true);
    }
}

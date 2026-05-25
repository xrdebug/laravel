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
use Chevere\xrDebug\Laravel\Watchers\DuplicateQueryWatcher;
use DateTime;
use Illuminate\Support\Facades\DB;

final class DuplicateQueryWatcherTest extends TestCase
{
    use QueryEventHelper;

    public function testDefaultsToDisabled(): void
    {
        $watcher = new DuplicateQueryWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testFirstQueryIsNotSent(): void
    {
        $this->dispatchQueryEvent('select * from users');
        $this->assertNoMessagesSent();
    }

    public function testSecondIdenticalQueryIsSent(): void
    {
        $this->dispatchQueryEvent('select * from users');
        $this->dispatchQueryEvent('select * from users');
        $this->assertSentCount(1);
    }

    public function testTopicIsDuplicate(): void
    {
        $this->dispatchQueryEvent('select * from users');
        $this->dispatchQueryEvent('select * from users');
        $this->assertSame('Query', $this->lastMessage()->topic());
    }

    public function testDifferentQueriesAreNotDuplicates(): void
    {
        $this->dispatchQueryEvent('select * from users');
        $this->dispatchQueryEvent('select * from posts');
        $this->assertNoMessagesSent();
    }

    public function testSeenQueriesAreTracked(): void
    {
        /** @var DuplicateQueryWatcher $watcher */
        $watcher = $this->app->make(DuplicateQueryWatcher::class);
        $this->dispatchQueryEvent('select * from users');
        $this->dispatchQueryEvent('select * from posts');
        $this->assertCount(2, $watcher->getSeenQueries());
    }

    public function testDisableWorks(): void
    {
        $watcher = $this->disableWatcher(DuplicateQueryWatcher::class);
        $this->assertFalse($watcher->isEnabled());
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(DuplicateQueryWatcher::class);
        $this->dispatchQueryEvent('select * from users');
        $this->dispatchQueryEvent('select * from users');
        $this->assertNoMessagesSent();
    }

    public function testEnableStartsQueryLogOnConnections(): void
    {
        DB::connection();
        /** @var DuplicateQueryWatcher $watcher */
        $watcher = $this->app->make(DuplicateQueryWatcher::class);
        $watcher->enable();
        $this->assertTrue(DB::connection()->logging());
    }

    public function testDisableStopsQueryLog(): void
    {
        DB::connection()->enableQueryLog();
        $this->assertTrue(DB::connection()->logging());
        /** @var DuplicateQueryWatcher $watcher */
        $watcher = $this->app->make(DuplicateQueryWatcher::class);
        $watcher->disable();
        $this->assertFalse(DB::connection()->logging());
    }

    public function testQueriesWithSameScalarBindingsAreDuplicates(): void
    {
        $this->dispatchQueryEvent('select * from users where id = ?', [1]);
        $this->dispatchQueryEvent('select * from users where id = ?', [1]);
        $this->assertSentCount(1);
    }

    public function testQueriesWithDifferentBindingsAreNotDuplicates(): void
    {
        $this->dispatchQueryEvent('select * from users where id = ?', [1]);
        $this->dispatchQueryEvent('select * from users where id = ?', [2]);
        $this->assertNoMessagesSent();
    }

    public function testDateTimeBindingNormalizedForDeduplication(): void
    {
        $dt = new DateTime('2024-01-15 12:00:00');
        $this->dispatchQueryEvent('select * from users where created_at = ?', [$dt]);
        $this->dispatchQueryEvent('select * from users where created_at = ?', [$dt]);
        $this->assertSentCount(1);
    }

    public function testNonScalarBindingNormalizedToEmptyString(): void
    {
        /** @var DuplicateQueryWatcher $watcher */
        $watcher = $this->app->make(DuplicateQueryWatcher::class);
        $this->dispatchQueryEvent('select * from users where id = ?', [['nested']]);
        $this->assertSame(['select * from users where id = '], $watcher->getSeenQueries());
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_duplicate_queries', true);
    }
}

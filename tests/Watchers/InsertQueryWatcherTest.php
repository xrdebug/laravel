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
use Chevere\xrDebug\Laravel\Watchers\InsertQueryWatcher;

final class InsertQueryWatcherTest extends TestCase
{
    use QueryEventHelper;

    public function testDefaultsToDisabled(): void
    {
        $watcher = new InsertQueryWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testInsertQueryIsSent(): void
    {
        $this->dispatchQueryEvent('insert into users (email) values (?)');
        $this->assertSentCount(1);
    }

    public function testTopicIsQueryInsert(): void
    {
        $this->dispatchQueryEvent('insert into users (email) values (?)');
        $this->assertSame('Query', $this->lastMessage()->topic());
    }

    public function testSelectQueryIsNotSent(): void
    {
        $this->dispatchQueryEvent('select * from users');
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(InsertQueryWatcher::class);
        $this->dispatchQueryEvent('insert into users (email) values (?)');
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_insert_queries', true);
    }
}

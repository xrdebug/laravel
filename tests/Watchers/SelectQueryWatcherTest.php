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
use Chevere\xrDebug\Laravel\Watchers\SelectQueryWatcher;

final class SelectQueryWatcherTest extends TestCase
{
    use QueryEventHelper;

    public function testDefaultsToDisabled(): void
    {
        $watcher = new SelectQueryWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testSelectQueryIsSent(): void
    {
        $this->dispatchQueryEvent('select * from users');
        $this->assertSentCount(1);
    }

    public function testTopicIsQuerySelect(): void
    {
        $this->dispatchQueryEvent('select * from users');
        $this->assertSame('Query', $this->lastMessage()->topic());
    }

    public function testInsertQueryIsNotSent(): void
    {
        $this->dispatchQueryEvent('insert into users values (1)');
        $this->assertNoMessagesSent();
    }

    public function testUpdateQueryIsNotSent(): void
    {
        $this->dispatchQueryEvent('update users set name = ?');
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(SelectQueryWatcher::class);
        $this->dispatchQueryEvent('select * from users');
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_select_queries', true);
    }
}

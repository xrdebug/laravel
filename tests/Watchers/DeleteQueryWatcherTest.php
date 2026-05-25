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
use Chevere\xrDebug\Laravel\Watchers\DeleteQueryWatcher;

final class DeleteQueryWatcherTest extends TestCase
{
    use QueryEventHelper;

    public function testDefaultsToDisabled(): void
    {
        $watcher = new DeleteQueryWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testDeleteQueryIsSent(): void
    {
        $this->dispatchQueryEvent('delete from users where id = ?');
        $this->assertSentCount(1);
    }

    public function testTopicIsQueryDelete(): void
    {
        $this->dispatchQueryEvent('delete from users where id = ?');
        $this->assertSame('Query', $this->lastMessage()->topic());
    }

    public function testSelectQueryIsNotSent(): void
    {
        $this->dispatchQueryEvent('select * from users');
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(DeleteQueryWatcher::class);
        $this->dispatchQueryEvent('delete from users where id = ?');
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_delete_queries', true);
    }
}

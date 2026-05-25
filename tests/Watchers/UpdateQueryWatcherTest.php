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
use Chevere\xrDebug\Laravel\Watchers\UpdateQueryWatcher;

final class UpdateQueryWatcherTest extends TestCase
{
    use QueryEventHelper;

    public function testDefaultsToDisabled(): void
    {
        $watcher = new UpdateQueryWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testUpdateQueryIsSent(): void
    {
        $this->dispatchQueryEvent('update users set email = ? where id = ?');
        $this->assertSentCount(1);
    }

    public function testTopicIsQueryUpdate(): void
    {
        $this->dispatchQueryEvent('update users set email = ? where id = ?');
        $this->assertSame('Query', $this->lastMessage()->topic());
    }

    public function testSelectQueryIsNotSent(): void
    {
        $this->dispatchQueryEvent('select * from users');
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(UpdateQueryWatcher::class);
        $this->dispatchQueryEvent('update users set email = ? where id = ?');
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_update_queries', true);
    }
}

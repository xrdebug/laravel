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
use Chevere\xrDebug\Laravel\Watchers\DeprecatedNoticeWatcher;
use Illuminate\Log\Events\MessageLogged;

final class DeprecatedNoticeWatcherTest extends TestCase
{
    public function testDefaultsToDisabled(): void
    {
        $watcher = new DeprecatedNoticeWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testIsDeprecationMessageReturnsTrueForDeprecated(): void
    {
        $watcher = new DeprecatedNoticeWatcher();
        $message = new MessageLogged('notice', 'Function foo() is deprecated', []);
        $this->assertTrue($watcher->isDeprecationMessage($message));
    }

    public function testIsDeprecationMessageReturnsTrueForCapitalizedDeprecated(): void
    {
        $watcher = new DeprecatedNoticeWatcher();
        $message = new MessageLogged('notice', 'Deprecated: This method will be removed', []);
        $this->assertTrue($watcher->isDeprecationMessage($message));
    }

    public function testIsDeprecationMessageReturnsTrueForReturnTypeWillChange(): void
    {
        $watcher = new DeprecatedNoticeWatcher();
        $message = new MessageLogged('notice', 'foo(): Return type will change [\ReturnTypeWillChange]', []);
        $this->assertTrue($watcher->isDeprecationMessage($message));
    }

    public function testIsDeprecationMessageReturnsFalseForPlainMessage(): void
    {
        $watcher = new DeprecatedNoticeWatcher();
        $message = new MessageLogged('info', 'Just a normal message', []);
        $this->assertFalse($watcher->isDeprecationMessage($message));
    }

    public function testDeprecationNoticeSendsMessage(): void
    {
        event(new MessageLogged('notice', 'Function bar() is deprecated since v2.0', []));
        $this->assertSentCount(1);
    }

    public function testTopicIsDeprecated(): void
    {
        event(new MessageLogged('notice', 'Function bar() is deprecated', []));
        $this->assertSame('Deprecated', $this->lastMessage()->topic());
    }

    public function testNonDeprecationLogIsNotSent(): void
    {
        event(new MessageLogged('info', 'Normal log message', []));
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(DeprecatedNoticeWatcher::class);
        event(new MessageLogged('notice', 'Function foo() is deprecated', []));
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_deprecated_notices', true);
        $app['config']->set('xr.show_logs', false);
    }
}

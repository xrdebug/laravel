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
use Chevere\xrDebug\Laravel\Watchers\ApplicationLogWatcher;
use Illuminate\Log\Events\MessageLogged;
use RuntimeException;

final class ApplicationLogWatcherTest extends TestCase
{
    public function testDefaultsToEnabled(): void
    {
        /** @var ApplicationLogWatcher $watcher */
        $watcher = $this->app->make(ApplicationLogWatcher::class);
        $this->assertTrue($watcher->isEnabled());
    }

    public function testPlainLogMessageIsSent(): void
    {
        event(new MessageLogged('info', 'plain log message', []));
        $this->assertSentCount(1);
    }

    public function testTopicIncludesLogLevel(): void
    {
        event(new MessageLogged('warning', 'something happened', []));
        $this->assertSame('Log', $this->lastMessage()->topic());
    }

    public function testBodyContainsMessage(): void
    {
        event(new MessageLogged('info', 'hello world', []));
        $this->assertBodyContains('hello world');
    }

    public function testExceptionLogIsNotForwarded(): void
    {
        event(new MessageLogged('error', 'an error', [
            'exception' => new RuntimeException('boom'),
        ]));
        $this->assertNoMessagesSent();
    }

    public function testMailLogIsNotForwarded(): void
    {
        $mailMessage = "Message-ID: <abc@example.com>\nTo: user@example.com\nContent: test";
        event(new MessageLogged('debug', $mailMessage, []));
        $this->assertNoMessagesSent();
    }

    public function testDeprecationLogIsNotForwarded(): void
    {
        event(new MessageLogged('notice', 'Function foo() is deprecated', []));
        $this->assertNoMessagesSent();
    }

    public function testNullMessageIsNotSent(): void
    {
        // @phpstan-ignore-next-line
        event(new MessageLogged('info', null, []));
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(ApplicationLogWatcher::class);
        event(new MessageLogged('info', 'a message', []));

        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_logs', true);
        $app['config']->set('xr.show_exceptions', false);
        $app['config']->set('xr.show_mails', false);
        $app['config']->set('xr.show_deprecated_notices', false);
    }
}

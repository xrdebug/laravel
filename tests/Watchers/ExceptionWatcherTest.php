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
use Chevere\xrDebug\Laravel\Watchers\ExceptionWatcher;
use Illuminate\Log\Events\MessageLogged;
use RuntimeException;

final class ExceptionWatcherTest extends TestCase
{
    public function testDefaultsToEnabled(): void
    {
        /** @var ExceptionWatcher $watcher */
        $watcher = $this->app->make(ExceptionWatcher::class);
        $this->assertTrue($watcher->isEnabled());
    }

    public function testConcernsExceptionReturnsTrueForThrowable(): void
    {
        $watcher = new ExceptionWatcher();
        $message = new MessageLogged('error', 'test', [
            'exception' => new RuntimeException('oops'),
        ]);
        $this->assertTrue($watcher->concernsException($message));
    }

    public function testConcernsExceptionReturnsFalseWithNoException(): void
    {
        $watcher = new ExceptionWatcher();
        $message = new MessageLogged('info', 'plain log', []);
        $this->assertFalse($watcher->concernsException($message));
    }

    public function testConcernsExceptionReturnsFalseForNonThrowable(): void
    {
        $watcher = new ExceptionWatcher();
        $message = new MessageLogged('error', 'test', [
            'exception' => 'not a throwable',
        ]);
        $this->assertFalse($watcher->concernsException($message));
    }

    public function testExceptionMessageIsSent(): void
    {
        event(new MessageLogged('error', 'an error occurred', [
            'exception' => new RuntimeException('test error'),
        ]));
        $this->assertSentCount(1);
    }

    public function testNonExceptionLogIsNotSent(): void
    {
        event(new MessageLogged('info', 'just a plain log', []));
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(ExceptionWatcher::class);
        event(new MessageLogged('error', 'an error occurred', [
            'exception' => new RuntimeException('test error'),
        ]));
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_exceptions', true);
        $app['config']->set('xr.show_logs', false);
        $app['config']->set('xr.show_deprecated_notices', false);
    }
}

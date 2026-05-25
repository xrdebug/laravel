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
use Chevere\xrDebug\Laravel\Watchers\CacheWatcher;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;

final class CacheWatcherTest extends TestCase
{
    public function testDefaultsToDisabled(): void
    {
        $watcher = new CacheWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testCacheHitSendsMessage(): void
    {
        event(new CacheHit('default', 'my-cache-key', 'value'));
        $this->assertSentCount(1);
    }

    public function testCacheHitTopic(): void
    {
        event(new CacheHit('default', 'my-cache-key', 'value'));
        $this->assertSame('Cache', $this->lastMessage()->topic());
    }

    public function testCacheHitBodyContainsKey(): void
    {
        event(new CacheHit('default', 'my-cache-key', 'value'));
        $this->assertBodyContains('my-cache-key');
    }

    public function testCacheMissedSendsMessage(): void
    {
        event(new CacheMissed('default', 'missing-key'));
        $this->assertSentCount(1);
    }

    public function testCacheMissedTopic(): void
    {
        event(new CacheMissed('default', 'missing-key'));
        $this->assertSame('Cache', $this->lastMessage()->topic());
    }

    public function testKeyWrittenSendsMessage(): void
    {
        event(new KeyWritten('default', 'written-key', 'value', 60));
        $this->assertSentCount(1);
    }

    public function testKeyWrittenTopic(): void
    {
        event(new KeyWritten('default', 'written-key', 'value', 60));
        $this->assertSame('Cache', $this->lastMessage()->topic());
    }

    public function testKeyForgottenSendsMessage(): void
    {
        event(new KeyForgotten('default', 'forgotten-key'));
        $this->assertSentCount(1);
    }

    public function testKeyForgottenTopic(): void
    {
        event(new KeyForgotten('default', 'forgotten-key'));
        $this->assertSame('Cache', $this->lastMessage()->topic());
    }

    public function testDisabledWatcherDoesNotSendOnHit(): void
    {
        $this->disableWatcher(CacheWatcher::class);
        event(new CacheHit('default', 'key', 'value'));
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSendOnMissed(): void
    {
        $this->disableWatcher(CacheWatcher::class);
        event(new CacheMissed('default', 'key'));
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSendOnWritten(): void
    {
        $this->disableWatcher(CacheWatcher::class);
        event(new KeyWritten('default', 'key', 'value', 60));
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSendOnForgotten(): void
    {
        $this->disableWatcher(CacheWatcher::class);
        event(new KeyForgotten('default', 'key'));
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_cache', true);
    }
}

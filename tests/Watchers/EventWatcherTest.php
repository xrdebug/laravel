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
use Chevere\xrDebug\Laravel\Watchers\EventWatcher;

final class EventWatcherTest extends TestCase
{
    public function testDefaultsToDisabled(): void
    {
        $watcher = new EventWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testArbitraryEventSendsMessage(): void
    {
        event(new TestEvent());
        $this->assertTopicSent('Event');
    }

    public function testTopicIsEvent(): void
    {
        event(new TestEvent());
        $found = array_filter(
            $this->fakeClient->sentMessages,
            fn ($m) => $m->topic() === 'Event'
        );
        $this->assertNotEmpty($found);
    }

    public function testBodyContainsEventClassName(): void
    {
        event(new TestEvent());
        $found = array_filter(
            $this->fakeClient->sentMessages,
            fn ($m) => $m->topic() === 'Event' && str_contains($m->body(), 'TestEvent')
        );
        $this->assertNotEmpty($found);
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(EventWatcher::class);
        event(new TestEvent());
        $found = array_filter(
            $this->fakeClient->sentMessages,
            fn ($m) => $m->topic() === 'Event'
        );
        $this->assertEmpty($found);
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_events', true);
        // Disable other watchers so their events don't bleed into count assertions
        $app['config']->set('xr.show_exceptions', false);
        $app['config']->set('xr.show_logs', false);
        $app['config']->set('xr.show_mails', false);
        $app['config']->set('xr.show_dumps', false);
    }
}

class TestEvent
{
}

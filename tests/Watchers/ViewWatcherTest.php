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
use Chevere\xrDebug\Laravel\Watchers\ViewWatcher;
use Illuminate\View\View;

final class ViewWatcherTest extends TestCase
{
    public function testDefaultsToDisabled(): void
    {
        $watcher = new ViewWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testViewComposingSendsMessage(): void
    {
        $view = $this->makeViewMock('my-view');
        event('composing: my-view', [$view]);
        $this->assertSentCount(1);
    }

    public function testTopicIsView(): void
    {
        $view = $this->makeViewMock('my-view');
        event('composing: my-view', [$view]);
        $this->assertSame('View', $this->lastMessage()->topic());
    }

    public function testBodyContainsViewName(): void
    {
        $view = $this->makeViewMock('layouts.app');
        event('composing: layouts.app', [$view]);
        $this->assertBodyContains('layouts.app');
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(ViewWatcher::class);
        $view = $this->makeViewMock('my-view');
        event('composing: my-view', [$view]);
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_views', true);
    }

    private function makeViewMock(string $name): View
    {
        $mock = $this->createMock(View::class);
        $mock->method('getName')->willReturn($name);

        return $mock;
    }
}

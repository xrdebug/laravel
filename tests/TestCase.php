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

namespace Chevere\Tests;

use Chevere\Tests\TestClasses\FakeClient;
use Chevere\Tests\TestClasses\FakeXr;
use Chevere\xrDebug\Laravel\Watchers\Watcher;
use Chevere\xrDebug\Laravel\XrServiceProvider;
use Chevere\xrDebug\PHP\Interfaces\MessageInterface;
use Chevere\xrDebug\PHP\XrInstance;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected FakeClient $fakeClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeClient = new FakeClient();
        new XrInstance(new FakeXr($this->fakeClient));
    }

    /**
     * @param Application $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [XrServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('xr.enabled', true);
    }

    protected function assertSentCount(int $n): void
    {
        $this->assertCount($n, $this->fakeClient->sentMessages);
    }

    protected function assertNoMessagesSent(): void
    {
        $this->assertEmpty($this->fakeClient->sentMessages);
    }

    protected function assertTopicSent(string $topic): void
    {
        $topics = array_map(
            fn (MessageInterface $m) => $m->topic(),
            $this->fakeClient->sentMessages
        );
        $this->assertContains($topic, $topics);
    }

    protected function assertBodyContains(string $needle, int $index = 0): void
    {
        $this->assertStringContainsString(
            $needle,
            $this->fakeClient->sentMessages[$index]->body()
        );
    }

    protected function lastMessage(): MessageInterface
    {
        $this->assertNotEmpty($this->fakeClient->sentMessages, 'No messages were sent');

        return end($this->fakeClient->sentMessages);
    }

    protected function enableWatcher(string $class): Watcher
    {
        /** @var Watcher $watcher */
        $watcher = $this->app->make($class);
        $watcher->enable();

        return $watcher;
    }

    protected function disableWatcher(string $class): Watcher
    {
        /** @var Watcher $watcher */
        $watcher = $this->app->make($class);
        $watcher->disable();

        return $watcher;
    }
}

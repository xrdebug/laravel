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
use Chevere\xrDebug\Laravel\Watchers\DumpWatcher;
use Symfony\Component\VarDumper\VarDumper;

final class DumpWatcherTest extends TestCase
{
    private mixed $originalHandler;

    protected function setUp(): void
    {
        $this->originalHandler = VarDumper::setHandler(null);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        VarDumper::setHandler($this->originalHandler);
        parent::tearDown();
    }

    public function testDefaultsToEnabled(): void
    {
        /** @var DumpWatcher $watcher */
        $watcher = $this->app->make(DumpWatcher::class);
        $this->assertTrue($watcher->isEnabled());
    }

    public function testDumpSendsMessage(): void
    {
        dump('test-value');
        $this->assertSentCount(1);
    }

    public function testDisabledDoesNotOverrideHandler(): void
    {
        $app = $this->createApplication();
        $app['config']->set('xr.show_dumps', false);
        $sentinel = function (mixed $var): void {};
        VarDumper::setHandler($sentinel);
        $watcher = new DumpWatcher();
        $watcher->register();
        $this->assertSame($sentinel, VarDumper::setHandler(null));
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_dumps', true);
    }
}

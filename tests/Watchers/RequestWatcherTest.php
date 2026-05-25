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
use Chevere\xrDebug\Laravel\Watchers\RequestHandledWatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestWatcherTest extends TestCase
{
    public function testDefaultsToDisabled(): void
    {
        $watcher = new RequestHandledWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testRequestHandledSendsMessage(): void
    {
        event(new RequestHandled(
            Request::create('http://localhost/test', 'GET'),
            new Response('', 200)
        ));
        $this->assertSentCount(1);
    }

    public function testTopicIsRequest(): void
    {
        event(new RequestHandled(
            Request::create('http://localhost/test', 'GET'),
            new Response('', 200)
        ));
        $this->assertSame('Request Handled', $this->lastMessage()->topic());
    }

    public function testBodyContainsMethod(): void
    {
        event(new RequestHandled(
            Request::create('http://localhost/foo', 'POST'),
            new Response('', 200)
        ));
        $this->assertBodyContains('POST');
    }

    public function testBodyContainsUri(): void
    {
        event(new RequestHandled(
            Request::create('http://localhost/my-endpoint', 'GET'),
            new Response('', 200)
        ));
        $this->assertBodyContains('/my-endpoint');
    }

    public function testBodyContainsStatusCode(): void
    {
        event(new RequestHandled(
            Request::create('http://localhost/foo', 'GET'),
            new Response('', 404)
        ));
        $this->assertBodyContains('404');
    }

    public function testDisabledWatcherDoesNotSend(): void
    {
        $this->disableWatcher(RequestHandledWatcher::class);
        event(new RequestHandled(
            Request::create('http://localhost/test', 'GET'),
            new Response('', 200)
        ));
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_requests', true);
    }
}

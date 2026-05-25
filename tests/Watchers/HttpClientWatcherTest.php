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
use Chevere\xrDebug\Laravel\Watchers\HttpClientRequestWatcher;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;

final class HttpClientWatcherTest extends TestCase
{
    public function testDefaultsToDisabled(): void
    {
        $watcher = new HttpClientRequestWatcher();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testIsSupportedByCurrentLaravelVersion(): void
    {
        $this->assertTrue(HttpClientRequestWatcher::supportedByLaravelVersion());
    }

    public function testRegisterSkipsWhenUnsupportedLaravelVersion(): void
    {
        $this->disableWatcher(HttpClientRequestWatcher::class);
        $watcher = new class() extends HttpClientRequestWatcher {
            public static function supportedByLaravelVersion(): bool
            {
                return false;
            }
        };
        $watcher->register();
        event(new RequestSending($this->makeRequest()));
        $this->assertNoMessagesSent();
    }

    public function testRequestSendingSendsMessage(): void
    {
        event(new RequestSending($this->makeRequest()));
        $this->assertSentCount(1);
    }

    public function testRequestSendingTopic(): void
    {
        event(new RequestSending($this->makeRequest()));
        $this->assertSame('Http Client', $this->lastMessage()->topic());
    }

    public function testRequestSendingBodyContainsMethodAndUrl(): void
    {
        event(new RequestSending($this->makeRequest('GET', 'https://example.com/api')));
        $this->assertBodyContains('GET');
        $this->assertBodyContains('https://example.com/api');
    }

    public function testResponseReceivedSendsMessage(): void
    {
        $request = $this->makeRequest();
        $response = $this->makeResponse(200);
        event(new ResponseReceived($request, $response));
        $this->assertSentCount(1);
    }

    public function testResponseReceivedTopic(): void
    {
        event(new ResponseReceived($this->makeRequest(), $this->makeResponse(200)));
        $this->assertSame('Http Client', $this->lastMessage()->topic());
    }

    public function testResponseReceivedBodyContainsStatus(): void
    {
        event(new ResponseReceived(
            $this->makeRequest('GET', 'https://example.com/api'),
            $this->makeResponse(404)
        ));
        $this->assertBodyContains('404');
    }

    public function testDisabledWatcherDoesNotSendOnRequest(): void
    {
        $this->disableWatcher(HttpClientRequestWatcher::class);
        event(new RequestSending($this->makeRequest()));
        $this->assertNoMessagesSent();
    }

    public function testDisabledWatcherDoesNotSendOnResponse(): void
    {
        $this->disableWatcher(HttpClientRequestWatcher::class);
        event(new ResponseReceived($this->makeRequest(), $this->makeResponse(200)));
        $this->assertNoMessagesSent();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xr.show_http_client_requests', true);
    }

    private function makeRequest(string $method = 'GET', string $url = 'https://example.com'): Request
    {
        return new Request(new GuzzleRequest($method, $url));
    }

    private function makeResponse(int $status = 200): Response
    {
        return new Response(new GuzzleResponse($status));
    }
}

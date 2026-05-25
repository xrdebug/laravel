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

namespace Chevere\Tests\Payloads;

use Chevere\Tests\TestCase;
use Chevere\xrDebug\Laravel\Payloads\RequestHandledPayload;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Symfony\Component\HttpFoundation\Response;

final class RequestPayloadTest extends TestCase
{
    public function testGetHtmlContainsMethod(): void
    {
        $html = $this->makePayload('GET', '/foo')->getHtml();
        $this->assertStringContainsString('GET', $html);
    }

    public function testGetHtmlContainsUri(): void
    {
        $html = $this->makePayload('GET', '/test-uri')->getHtml();
        $this->assertStringContainsString('/test-uri', $html);
    }

    public function testGetHtmlContainsStatus(): void
    {
        $html = $this->makePayload('GET', '/foo', 200)->getHtml();
        $this->assertStringContainsString('200', $html);
    }

    public function testGetHtmlContainsStatusCode404(): void
    {
        $html = $this->makePayload('POST', '/foo', 404)->getHtml();
        $this->assertStringContainsString('404', $html);
    }

    public function testGetHtmlIsTable(): void
    {
        $html = $this->makePayload('GET', '/foo')->getHtml();
        $this->assertStringStartsWith('<table>', $html);
        $this->assertStringEndsWith('</table>', $html);
    }

    #[RunInSeparateProcess]
    public function testGetHtmlUsesDurationFromLaravelStart(): void
    {
        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }
        $html = $this->makePayload('GET', '/foo')->getHtml();
        $this->assertStringContainsString('Duration', $html);
        $this->assertStringContainsString('ms', $html);
    }

    #[RunInSeparateProcess]
    public function testGetHtmlUsesDurationFromLaravelStartNull(): void
    {
        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', null);
        }
        $html = $this->makePayload('GET', '/foo')->getHtml();
        $this->assertStringNotContainsString('Duration', $html);
        $this->assertStringNotContainsString('ms', $html);
    }

    public function testGetHtmlEscapesValues(): void
    {
        $request = Request::create('http://localhost/<script>');
        $response = new Response('', 200);
        $html = (new RequestHandledPayload(new RequestHandled($request, $response)))->getHtml();
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    private function makePayload(string $method, string $uri, int $status = 200): RequestHandledPayload
    {
        $request = Request::create("http://localhost{$uri}", $method);
        $response = new Response('', $status);

        return new RequestHandledPayload(new RequestHandled($request, $response));
    }
}

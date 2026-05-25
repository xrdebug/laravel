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

namespace Chevere\xrDebug\Laravel\Watchers;

use Chevere\xrDebug\Laravel\Payloads\TableHtml;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Event;

class HttpClientRequestWatcher extends Watcher
{
    public function register(): void
    {
        if (! static::supportedByLaravelVersion()) {
            return;
        }
        $this->isEnabled = (bool) config('xr.show_http_client_requests', false);
        Event::listen(
            RequestSending::class,
            function (RequestSending $event): void {
                if (! $this->isEnabled()) {
                    return;
                }
                xrr(
                    (string) new TableHtml(
                        [
                            'Method' => $event->request->method(),
                            'URL' => $event->request->url(),
                            'Headers' => $event->request->headers(),
                            'Data' => $event->request->data(),
                            'Body' => $event->request->body(),
                            'Type' => $this->getRequestType($event->request),
                        ]
                    ),
                    t: 'Http Client',
                    e: '✻ Request'
                );
            }
        );
        Event::listen(
            ResponseReceived::class,
            function (ResponseReceived $event): void {
                if (! $this->isEnabled()) {
                    return;
                }
                $response = $event->response;
                xrr(
                    (string) new TableHtml(
                        [
                            'URL' => $event->request->url(),
                            'Real Request' => ! empty($response->handlerStats()),
                            'Success' => $response->successful(),
                            'Status' => $response->status(),
                            'Headers' => $response->headers(),
                            'Body' => rescue(
                                function () use ($response) {
                                    return $response->json();
                                },
                                $response->body(),
                                false
                            ),
                            'Cookies' => $response->cookies(),
                            'Size' => $response->handlerStats()['size_download'] ?? null,
                            'Connection time' => $response->handlerStats()['connect_time'] ?? null,
                            'Duration' => $response->handlerStats()['total_time'] ?? null,
                            'Request Size' => $response->handlerStats()['request_size'] ?? null,
                        ]
                    ),
                    t: 'Http Client',
                    e: '⬅ Response'
                );
            }
        );
    }

    public static function supportedByLaravelVersion(): bool
    {
        return version_compare(app()->version(), '8.46.0', '>=');
    }

    protected function getRequestType(Request $request): string
    {
        return match (true) {
            $request->isJson() => 'Json',
            $request->isMultipart() => 'Multipart',
            default => 'Form',
        };
    }
}

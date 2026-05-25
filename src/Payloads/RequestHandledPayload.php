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

namespace Chevere\xrDebug\Laravel\Payloads;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class RequestHandledPayload
{
    private Request $request;

    private Response $response;

    public function __construct(
        RequestHandled $requestHandled,
    ) {
        $this->request = $requestHandled->request;
        $this->response = $requestHandled->response;
    }

    public function getHtml(): string
    {
        $startTime = defined('LARAVEL_START')
            ? LARAVEL_START
            : $this->request->server('REQUEST_TIME_FLOAT');
        $duration = $startTime !== null
            ? (int) floor((microtime(true) - (float) $startTime) * 1000)
            : null;
        $headers = collect($this->request->headers->all())
            ->map(
                function (array $header): mixed {
                    return $header[0];
                }
            )
            ->toArray();
        $session = $this->request->hasSession()
            ? $this->request->session()->all()
            : [];
        $rows = [
            'IP Address' => $this->request->ip(),
            'URI' => str_replace($this->request->root(), '', $this->request->fullUrl()) ?: '/',
            'Method' => $this->request->method(),
            /** @phpstan-ignore-next-line */
            'Controller action' => optional($this->request->route())->getActionName(),
            /** @phpstan-ignore-next-line */
            'Middleware' => array_values(optional($this->request->route())->gatherMiddleware() ?? []),
            'Headers' => $headers,
            'Payload' => $this->payload($this->request),
            'Session' => $session,
            'Response code' => $this->response->getStatusCode(),
            'Response' => $this->response($this->response),
            'Duration' => $duration !== null ? $duration . 'ms' : null,
            'Memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 1),
        ];

        return (string) new TableHtml($rows);
    }

    protected function response(Response $response): mixed
    {
        $content = $response->getContent();
        if (is_string($content)) {
            if (is_array(json_decode($content, true)) &&
                json_last_error() === JSON_ERROR_NONE) {
                return json_decode($content, true);
            }
            if (Str::startsWith(strtolower($response->headers->get('Content-Type') ?? ''), 'text/plain')) {
                return $content;
            }
        }
        if ($response instanceof RedirectResponse) {
            return 'Redirected to ' . $response->getTargetUrl();
        }
        if ($response instanceof IlluminateResponse
            && $response->getOriginalContent() instanceof View
        ) {
            return [
                'view' => $response->getOriginalContent()->getPath(),
                'data' => $this->extractDataFromView($response->getOriginalContent()),
            ];
        }

        return 'HTML Response';
    }

    /**
     * @return array<mixed, mixed>
     */
    protected function extractDataFromView(View $view): array
    {
        return collect($view->getData())
            ->map(
                function (mixed $value): array {
                    if ($value instanceof Model) {
                        return $value->toArray();
                    }
                    if (is_object($value)) {
                        return [
                            'class' => get_class($value),
                            'properties' => json_decode(
                                json_encode($value) ?: '{}',
                                true
                            ),
                        ];
                    }

                    /** @var array<mixed, mixed> */
                    return json_decode(
                        json_encode($value) ?: '{}',
                        true
                    );
                }
            )
            ->toArray();
    }

    /**
     * @return array<mixed, mixed>
     */
    private function payload(Request $request): array
    {
        $files = $request->files->all();
        array_walk_recursive(
            $files,
            function (UploadedFile &$file) {
                $file = [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->isFile()
                        ? (($file->getSize() / 1000) . 'KB')
                        : '0',
                ];
            }
        );
        $input = $request->input();

        return array_replace_recursive(
            is_array($input) ? $input : [],
            $files
        );
    }
}

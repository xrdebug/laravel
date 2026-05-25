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

namespace Chevere\Tests\TestClasses;

use Chevere\xrDebug\PHP\Interfaces\ClientInterface;
use Chevere\xrDebug\PHP\Interfaces\XrInterface;

final class FakeXr implements XrInterface
{
    public function __construct(
        private readonly FakeClient $client,
        private readonly bool $enabled = true,
    ) {}

    public function withConfigDir(string $config): XrInterface
    {
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isHttps(): bool
    {
        return false;
    }

    public function host(): string
    {
        return 'localhost';
    }

    public function port(): int
    {
        return 27420;
    }

    public function key(): string
    {
        return '';
    }

    public function client(): ClientInterface
    {
        return $this->client;
    }
}

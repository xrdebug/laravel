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
use Chevere\xrDebug\PHP\Interfaces\CurlInterface;
use Chevere\xrDebug\PHP\Interfaces\MessageInterface;
use RuntimeException;

final class FakeClient implements ClientInterface
{
    /**
     * @var list<MessageInterface>
     */
    public array $sentMessages = [];

    /**
     * @var list<MessageInterface>
     */
    public array $sentPauses = [];

    public function sendMessage(MessageInterface $message): void
    {
        $this->sentMessages[] = $message;
    }

    public function sendPause(MessageInterface $message): void
    {
        $this->sentPauses[] = $message;
    }

    public function isPaused(string $id): bool
    {
        return false;
    }

    public function curl(): CurlInterface
    {
        return new FakeCurl();
    }

    public function getUrl(string $endpoint): string
    {
        return "http://fake-xr:27420/{$endpoint}";
    }

    public function options(): array
    {
        return [];
    }

    public function exit(int $exitCode = 0): void
    {
        throw new RuntimeException("exit({$exitCode}) called in test context");
    }
}

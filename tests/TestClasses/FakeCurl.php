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

use Chevere\xrDebug\PHP\Interfaces\CurlInterface;
use CurlHandle;

final class FakeCurl implements CurlInterface
{
    public function __destruct() {}

    public function handle(): ?CurlHandle
    {
        return null;
    }

    public function error(): string
    {
        return '';
    }

    public function exec(): string|bool
    {
        return '';
    }

    public function setOptArray(array $options): bool
    {
        return true;
    }

    public function close(): void {}
}

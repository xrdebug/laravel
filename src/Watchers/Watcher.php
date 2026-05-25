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

abstract class Watcher
{
    protected bool $isEnabled = false;

    abstract public function register(): void;

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function enable(): static
    {
        $this->isEnabled = true;

        return $this;
    }

    public function disable(): static
    {
        $this->isEnabled = false;

        return $this;
    }
}

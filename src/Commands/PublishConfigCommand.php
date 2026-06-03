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

namespace Chevere\xrDebug\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishConfigCommand extends Command
{
    protected $signature = 'xr:publish-config {--homestead : Indicates that Homestead is being used}
                                              {--docker : Indicates that Docker is being used}';

    protected $description = 'Create the Laravel xr config file in project root.';

    public function handle(): int
    {
        $target = base_path('xr.php');
        if ((new Filesystem())->exists($target)) {
            $this->error('xr.php already exists in the project root');

            return 1;
        }
        copy(__DIR__ . '/../../config/xr.php', $target);
        if ($this->option('docker')) {
            file_put_contents(
                $target,
                str_replace(
                    "'host' => env('XR_HOST', 'localhost')",
                    "'host' => env('XR_HOST', 'host.docker.internal')",
                    (string) file_get_contents($target)
                )
            );
        }
        if ($this->option('homestead')) {
            file_put_contents(
                $target,
                str_replace(
                    "'host' => env('XR_HOST', 'localhost')",
                    "'host' => env('XR_HOST', '10.0.2.2')",
                    (string) file_get_contents($target)
                )
            );
        }
        $this->info('`xr.php` created in the project root');

        return 0;
    }
}

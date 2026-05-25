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

namespace Chevere\Tests\Commands;

use Chevere\Tests\TestCase;

final class PublishConfigCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->deleteGeneratedConfig();
        parent::tearDown();
    }

    public function testCreatesConfigFile(): void
    {
        $this->artisan('xr:publish-config')
            ->expectsOutput('`xr.php` created in the project root')
            ->assertExitCode(0);
        $path = $this->configPath();
        $this->assertFileExists($path);
        $this->assertStringContainsString(
            "'host' => env('XR_HOST', 'localhost')",
            (string) file_get_contents($path)
        );
    }

    public function testCreatesDockerConfigFile(): void
    {
        $this->artisan('xr:publish-config --docker')
            ->expectsOutput('`xr.php` created in the project root')
            ->assertExitCode(0);
        $path = $this->configPath();
        $this->assertFileExists($path);
        $this->assertStringContainsString(
            "'host' => env('XR_HOST', 'host.docker.internal')",
            (string) file_get_contents($path)
        );
    }

    public function testCreatesHomesteadConfigFile(): void
    {
        $this->artisan('xr:publish-config --homestead')
            ->expectsOutput('`xr.php` created in the project root')
            ->assertExitCode(0);
        $path = $this->configPath();
        $this->assertFileExists($path);
        $this->assertStringContainsString(
            "'host' => env('XR_HOST', '10.0.2.2')",
            (string) file_get_contents($path)
        );
    }

    public function testFailsWhenConfigAlreadyExists(): void
    {
        file_put_contents($this->configPath(), 'existing');
        $this->artisan('xr:publish-config')
            ->expectsOutput('xr.php already exists in the project root')
            ->assertExitCode(1);
    }

    private function configPath(): string
    {
        return base_path('xr.php');
    }

    private function deleteGeneratedConfig(): void
    {
        $path = $this->configPath();
        if (is_file($path)) {
            unlink($path);
        }
    }
}

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

namespace Chevere\xrDebug\Laravel;

use Chevere\xrDebug\Laravel\Commands\PublishConfigCommand;
use Chevere\xrDebug\Laravel\Interfaces\XrInspectorInterface;
use Chevere\xrDebug\Laravel\Watchers\ApplicationLogWatcher;
use Chevere\xrDebug\Laravel\Watchers\CacheWatcher;
use Chevere\xrDebug\Laravel\Watchers\DeleteQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\DeprecatedNoticeWatcher;
use Chevere\xrDebug\Laravel\Watchers\DumpWatcher;
use Chevere\xrDebug\Laravel\Watchers\DuplicateQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\EventWatcher;
use Chevere\xrDebug\Laravel\Watchers\ExceptionWatcher;
use Chevere\xrDebug\Laravel\Watchers\HttpClientRequestWatcher;
use Chevere\xrDebug\Laravel\Watchers\InsertQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\JobWatcher;
use Chevere\xrDebug\Laravel\Watchers\MailWatcher;
use Chevere\xrDebug\Laravel\Watchers\QueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\RequestHandledWatcher;
use Chevere\xrDebug\Laravel\Watchers\SelectQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\SlowQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\UpdateQueryWatcher;
use Chevere\xrDebug\Laravel\Watchers\ViewWatcher;
use Chevere\xrDebug\Laravel\Watchers\Watcher;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Throwable;

class XrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/xr.php', 'xr');
        $this->mergeProjectConfigFile();
        $this->app->singleton(XrInspector::class);
        $this->app->singleton(
            XrInspectorInterface::class,
            XrInspector::class
        );
        xrConfig(
            isEnabled: (bool) config('xr.enabled', true),
            isHttps: (bool) config('xr.https', false),
            /** @phpstan-ignore-next-line */
            host: (string) config('xr.host', 'localhost'),
            /** @phpstan-ignore-next-line */
            port: (int) config('xr.port', 27420),
            /** @phpstan-ignore-next-line */
            key: (string) config('xr.key', ''),
            /** @phpstan-ignore-next-line */
            localPath: (string) config('xr.local_path', ''),
            /** @phpstan-ignore-next-line */
            remotePath: (string) config('xr.remote_path', ''),
        );
        foreach ($this->watchers() as $watcher) {
            $this->app->singleton($watcher);
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([PublishConfigCommand::class]);
        }
        foreach ($this->watchers() as $watcher) {
            $instance = $this->app->make($watcher);
            assert($instance instanceof Watcher);
            $instance->register();
        }
    }

    /**
     * @return class-string[]
     */
    public function watchers(): array
    {
        return [
            ApplicationLogWatcher::class,
            CacheWatcher::class,
            DeleteQueryWatcher::class,
            DeprecatedNoticeWatcher::class,
            DuplicateQueryWatcher::class,
            DumpWatcher::class,
            EventWatcher::class,
            ExceptionWatcher::class,
            HttpClientRequestWatcher::class,
            InsertQueryWatcher::class,
            JobWatcher::class,
            MailWatcher::class,
            QueryWatcher::class,
            RequestHandledWatcher::class,
            SelectQueryWatcher::class,
            SlowQueryWatcher::class,
            UpdateQueryWatcher::class,
            ViewWatcher::class,
        ];
    }

    private function mergeProjectConfigFile(): void
    {
        $configFile = $this->searchProjectConfigFile();
        if ($configFile === '') {
            return;
        }

        try {
            $projectConfig = require $configFile;
        } catch (Throwable) {
            return;
        }
        if (! is_array($projectConfig)) {
            return;
        }
        /** @var Repository $config */
        $config = $this->app->make('config');
        $config->set('xr', array_replace((array) $config->get('xr', []), $projectConfig));
    }

    private function searchProjectConfigFile(): string
    {
        $configDirectory = $this->app->configPath();
        while (is_dir($configDirectory)) {
            $configFile = $configDirectory . DIRECTORY_SEPARATOR . 'xr.php';
            if (file_exists($configFile)) {
                return $configFile;
            }
            $parentDirectory = dirname($configDirectory);
            if ($parentDirectory === $configDirectory) {
                return '';
            }
            $configDirectory = $parentDirectory;
        }

        return '';
    }
}

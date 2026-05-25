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

namespace Chevere\Tests;

use Chevere\xrDebug\Laravel\Interfaces\XrInspectorInterface;
use Chevere\xrDebug\Laravel\Watchers\ApplicationLogWatcher;
use Chevere\xrDebug\Laravel\Watchers\CacheWatcher;
use Chevere\xrDebug\Laravel\Watchers\ConditionalQueryWatcher;
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
use Chevere\xrDebug\Laravel\XrInspector;
use Chevere\xrDebug\Laravel\XrServiceProvider;
use Illuminate\Support\Facades\DB;

final class XrServiceProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->deleteProjectConfigFile();
        $this->deleteConfigDirectoryFile();
        parent::tearDown();
    }

    public function testConfigMerged(): void
    {
        $this->assertTrue($this->app['config']->has('xr'));
        $this->assertTrue($this->app['config']->get('xr.enabled'));
    }

    public function testLoadsProjectRootConfigFileWhenPresent(): void
    {
        $this->writeProjectConfigFile([
            'host' => 'root-host.test',
        ]);
        $this->app['config']->set('xr', []);

        (new XrServiceProvider($this->app))->register();

        $this->assertSame('root-host.test', config('xr.host'));
        $this->assertTrue(config('xr.show_dumps'));
    }

    public function testConfigDirectoryFileTakesPrecedenceOverProjectRootFile(): void
    {
        $this->writeProjectConfigFile([
            'host' => 'root-host.test',
        ]);
        $this->writeConfigDirectoryFile([
            'host' => 'config-host.test',
        ]);
        $this->app['config']->set('xr', []);

        (new XrServiceProvider($this->app))->register();

        $this->assertSame('config-host.test', config('xr.host'));
    }

    public function testWatchersSingleton(): void
    {
        foreach ($this->watcherClasses() as $class) {
            $a = $this->app->make($class);
            $b = $this->app->make($class);
            $this->assertSame($a, $b, "{$class} is not a singleton");
        }
    }

    public function testWatchersAreWatcherInstances(): void
    {
        foreach ($this->watcherClasses() as $class) {
            $instance = $this->app->make($class);
            $this->assertInstanceOf(Watcher::class, $instance);
        }
    }

    public function testWatchersAreWatcherClasses(): void
    {
        $provider = new XrServiceProvider($this->app);
        foreach ($provider->watchers() as $class) {
            $this->assertTrue(
                is_subclass_of($class, Watcher::class),
                "{$class} is not a subclass of Watcher"
            );
        }
    }

    public function testDefaultConfigFlags(): void
    {
        $this->assertTrue($this->app['config']->get('xr.show_exceptions'));
        $this->assertTrue($this->app['config']->get('xr.show_logs'));
        $this->assertTrue($this->app['config']->get('xr.show_mails'));
        $this->assertTrue($this->app['config']->get('xr.show_dumps'));
        $this->assertFalse($this->app['config']->get('xr.show_queries'));
        $this->assertFalse($this->app['config']->get('xr.show_cache'));
        $this->assertFalse($this->app['config']->get('xr.show_events'));
        $this->assertFalse($this->app['config']->get('xr.show_jobs'));
    }

    public function testEnabledDefaultsToFalseInProductionWhenConfigMissing(): void
    {
        putenv('XR_ENABLED');
        unset($_ENV['XR_ENABLED'], $_SERVER['XR_ENABLED']);
        putenv('APP_ENV=production');
        $_ENV['APP_ENV'] = 'production';
        $_SERVER['APP_ENV'] = 'production';
        $this->app['config']->set('xr', []);
        (new XrServiceProvider($this->app))->register();
        $this->assertFalse(\Chevere\xrDebug\PHP\getXrFailover()?->isEnabled());
    }

    public function testEnabledDefaultsToTrueOutsideProductionWhenConfigMissing(): void
    {
        putenv('XR_ENABLED');
        unset($_ENV['XR_ENABLED'], $_SERVER['XR_ENABLED']);
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';
        $_SERVER['APP_ENV'] = 'testing';
        $this->app['config']->set('xr', []);
        (new XrServiceProvider($this->app))->register();
        $this->assertTrue(\Chevere\xrDebug\PHP\getXrFailover()?->isEnabled());
    }

    public function testEnabledFollowsExplicitConfigValue(): void
    {
        $this->app['config']->set('xr.enabled', false);
        $this->app['env'] = 'testing';
        (new XrServiceProvider($this->app))->register();
        $this->assertFalse(\Chevere\xrDebug\PHP\getXrFailover()?->isEnabled());
    }

    public function testInspectorBindingsAreSingletons(): void
    {
        $inspectorByClassA = $this->app->make(XrInspector::class);
        $inspectorByClassB = $this->app->make(XrInspector::class);
        $inspectorByInterface = $this->app->make(XrInspectorInterface::class);
        $this->assertSame($inspectorByClassA, $inspectorByClassB);
        $this->assertSame($inspectorByClassA, $inspectorByInterface);
    }

    public function testInspectorControlsWatcherStateInline(): void
    {
        /** @var XrInspectorInterface $inspector */
        $inspector = $this->app->make(XrInspectorInterface::class);
        /** @var MailWatcher $mailWatcher */
        $mailWatcher = $this->app->make(MailWatcher::class);
        $this->assertTrue($mailWatcher->isEnabled());
        $inspector->stopShowingMails();
        $this->assertFalse($mailWatcher->isEnabled());
        $inspector->showMails();
        $this->assertTrue($mailWatcher->isEnabled());
    }

    public function testInspectorControlsAllOtherWatcherMethodsInline(): void
    {
        /** @var XrInspectorInterface $inspector */
        $inspector = $this->app->make(XrInspectorInterface::class);
        foreach ($this->inspectorShowStopMethodsAndWatchers() as [$showMethod, $stopMethod, $watcherClass]) {
            /** @var Watcher $watcher */
            $watcher = $this->app->make($watcherClass);
            $watcher->enable();
            $inspector->{$stopMethod}();
            $this->assertFalse($watcher->isEnabled(), "{$stopMethod} did not disable {$watcherClass}");
            $inspector->{$showMethod}();
            if ($watcherClass === HttpClientRequestWatcher::class && ! HttpClientRequestWatcher::supportedByLaravelVersion()) {
                $this->assertFalse($watcher->isEnabled(), "{$showMethod} should not enable unsupported {$watcherClass}");

                continue;
            }

            $this->assertTrue($watcher->isEnabled(), "{$showMethod} did not enable {$watcherClass}");
        }
    }

    public function testInspectorControlsConditionalQueryWatcherStateInline(): void
    {
        /** @var XrInspectorInterface $inspector */
        $inspector = $this->app->make(XrInspectorInterface::class);
        $this->expectException(\Error::class);
        $inspector->showConditionalQueries(static fn () => true);
    }

    public function testInspectorCanStopConditionalQueryWatcherForBoundName(): void
    {
        /** @var XrInspectorInterface $inspector */
        $inspector = $this->app->make(XrInspectorInterface::class);
        $watcher = new class() extends QueryWatcher {
            public function register(): void
            {
            }
        };
        $watcher->enable();
        $this->app->instance(ConditionalQueryWatcher::abstractName('default'), $watcher);
        $inspector->stopShowingConditionalQueries();
        $this->assertFalse($watcher->isEnabled());
    }

    public function testInspectorCountQueriesSendsStatistics(): void
    {
        /** @var XrInspectorInterface $inspector */
        $inspector = $this->app->make(XrInspectorInterface::class);
        $return = $inspector->countQueries(
            function (): void {
                DB::select('select 1');
            }
        );
        $this->assertSame($inspector, $return);
        $this->assertTopicSent('Query statistics');
    }

    public function testHelperResolvesInspectorSingleton(): void
    {
        $helperInspector = xrLaravel();
        $boundInspector = $this->app->make(XrInspectorInterface::class);
        $this->assertSame($boundInspector, $helperInspector);
    }

    public function testHelperCanControlWatchersInline(): void
    {
        /** @var MailWatcher $mailWatcher */
        $mailWatcher = $this->app->make(MailWatcher::class);
        $this->assertTrue($mailWatcher->isEnabled());
        xrLaravel()->stopShowingMails();
        $this->assertFalse($mailWatcher->isEnabled());
        xrLaravel()->showMails();
        $this->assertTrue($mailWatcher->isEnabled());
    }

    /**
     * @return list<array{string, string, class-string<Watcher>}>
     */
    private function inspectorShowStopMethodsAndWatchers(): array
    {
        return [
            ['showEvents', 'stopShowingEvents', EventWatcher::class],
            ['showExceptions', 'stopShowingExceptions', ExceptionWatcher::class],
            ['showJobs', 'stopShowingJobs', JobWatcher::class],
            ['showCache', 'stopShowingCache', CacheWatcher::class],
            ['showViews', 'stopShowingViews', ViewWatcher::class],
            ['showQueries', 'stopShowingQueries', QueryWatcher::class],
            ['showSlowQueries', 'stopShowingSlowQueries', SlowQueryWatcher::class],
            ['showDuplicateQueries', 'stopShowingDuplicateQueries', DuplicateQueryWatcher::class],
            ['showUpdateQueries', 'stopShowingUpdateQueries', UpdateQueryWatcher::class],
            ['showDeleteQueries', 'stopShowingDeleteQueries', DeleteQueryWatcher::class],
            ['showInsertQueries', 'stopShowingInsertQueries', InsertQueryWatcher::class],
            ['showSelectQueries', 'stopShowingSelectQueries', SelectQueryWatcher::class],
            ['showRequests', 'stopShowingRequests', RequestHandledWatcher::class],
            ['showHttpClientRequests', 'stopShowingHttpClientRequests', HttpClientRequestWatcher::class],
        ];
    }

    /**
     * @return list<class-string<Watcher>>
     */
    private function watcherClasses(): array
    {
        return [
            ApplicationLogWatcher::class,
            CacheWatcher::class,
            DeleteQueryWatcher::class,
            DeprecatedNoticeWatcher::class,
            DumpWatcher::class,
            DuplicateQueryWatcher::class,
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

    /**
     * @param array<string, mixed> $config
     */
    private function writeProjectConfigFile(array $config): void
    {
        file_put_contents(
            base_path('xr.php'),
            '<?php return ' . var_export($config, true) . ';'
        );
    }

    /**
     * @param array<string, mixed> $config
     */
    private function writeConfigDirectoryFile(array $config): void
    {
        $path = $this->configDirectoryFilePath();
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents(
            $path,
            '<?php return ' . var_export($config, true) . ';'
        );
    }

    private function deleteProjectConfigFile(): void
    {
        $path = base_path('xr.php');
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function deleteConfigDirectoryFile(): void
    {
        $path = $this->configDirectoryFilePath();
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function configDirectoryFilePath(): string
    {
        return $this->app->configPath('xr.php');
    }
}

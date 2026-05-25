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

namespace Chevere\Tests\Payloads;

use Chevere\Tests\TestCase;
use Chevere\xrDebug\Laravel\Payloads\ExecutedQueryPayload;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

final class ExecutedQueryPayloadTest extends TestCase
{
    public function testGetContentHasSqlKey(): void
    {
        $event = $this->makeQueryEvent('select 1');
        $content = (new ExecutedQueryPayload($event))->getContent();
        $this->assertArrayHasKey('sql', $content);
    }

    public function testSqlIsSubstitutedWhenGrammarSupports(): void
    {
        $event = $this->makeQueryEvent('select * from users where id = ?', [42]);
        $content = (new ExecutedQueryPayload($event))->getContent();
        $this->assertStringContainsString('42', $content['sql']);
        $this->assertStringNotContainsString('?', $content['sql']);
    }

    public function testGetContentIncludesConnectionNameAndTimeWhenPresent(): void
    {
        $event = $this->makeQueryEvent('select 1', [], 12.5);
        $content = (new ExecutedQueryPayload($event))->getContent();
        $this->assertArrayHasKey('connection_name', $content);
        $this->assertArrayHasKey('time', $content);
        $this->assertSame(12.5, $content['time']);
    }

    public function testGetContentOmitsConnectionNameAndTimeWhenNull(): void
    {
        $event = $this->makeQueryEvent('select 1', [], null);
        $content = (new ExecutedQueryPayload($event))->getContent();
        $this->assertArrayNotHasKey('connection_name', $content);
        $this->assertArrayNotHasKey('time', $content);
    }

    public function testGetContentFallsBackToBindingsWhenGrammarLacksSubstitution(): void
    {
        // Use a plain object without substituteBindingsIntoRawSql to trigger the fallback branch
        $grammar = new class {};
        $connection = $this->createMock(Connection::class);
        $connection->method('getQueryGrammar')->willReturn($grammar);

        $event = new QueryExecuted('select * from users where id = ?', [99], null, $connection);
        $content = (new ExecutedQueryPayload($event))->getContent();

        $this->assertSame('select * from users where id = ?', $content['sql']);
        $this->assertSame([99], $content['bindings']);
        $this->assertArrayNotHasKey('connection_name', $content);
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    private function makeQueryEvent(string $sql, array $bindings = [], ?float $time = 10.0): QueryExecuted
    {
        return new QueryExecuted($sql, $bindings, $time, DB::connection());
    }
}

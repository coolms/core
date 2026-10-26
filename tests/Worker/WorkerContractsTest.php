<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Worker;

use CoolMS\Core\Worker\WorkerExpectationInterface;
use CoolMS\Core\Worker\WorkerHealthRecorderInterface;
use CoolMS\Core\Worker\WorkerRestartSignalInterface;
use CoolMS\Core\Worker\WorkerStartReporterInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function basename;
use function glob;
use function interface_exists;
use function sort;

/**
 * A long-running worker's contracts: how it says it is alive and how its work goes, how it learns it must leave,
 * and when work from it is expected. Interfaces only -- storing what workers report and showing it belong to a
 * module, never to core.
 */
final class WorkerContractsTest extends TestCase
{
    #[Test]
    public function theWorkerNamespaceHoldsInterfacesOnly(): void
    {
        $files = glob(__DIR__ . '/../../src/Worker/*.php') ?: [];
        self::assertNotEmpty($files);
        foreach ($files as $file) {
            $class = 'CoolMS\\Core\\Worker\\' . basename($file, '.php');
            self::assertTrue(interface_exists($class), $class . ' is not an interface');
        }
    }

    #[Test]
    public function eachContractSaysWhatItSaysAndNoMore(): void
    {
        self::assertSame(['failed', 'succeeded'], self::methods(WorkerHealthRecorderInterface::class));
        self::assertSame(['started'], self::methods(WorkerStartReporterInterface::class));
        $restart = self::methods(WorkerRestartSignalInterface::class);
        self::assertSame(['generation', 'request', 'requestedSince'], $restart);
        self::assertSame(['expectsWorkWithinSeconds', 'worker'], self::methods(WorkerExpectationInterface::class));
    }

    #[Test]
    public function expectationsAreCollectedUnderOneTag(): void
    {
        self::assertSame('coolms.worker_expectation', WorkerExpectationInterface::TAG);
    }

    /**
     * @param class-string $interface
     *
     * @return list<string>
     */
    private static function methods(string $interface): array
    {
        $names = array_map(
            static fn (ReflectionMethod $method): string => $method->getName(),
            new ReflectionClass($interface)->getMethods(),
        );
        sort($names);

        return $names;
    }
}

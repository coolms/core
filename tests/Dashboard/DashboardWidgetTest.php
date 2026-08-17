<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Dashboard;

use CoolMS\Core\Dashboard\DashboardWidget;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * The one rule the widget enforces on itself: its WIDTH.
 *
 * Everything else a widget claims is checked by the registry, which can see the
 * other widgets and the viewer. A width needs neither, and the registry could
 * not check it in any case — the widget is constructed inside its module's
 * provider, upstream of anything the catalogue sees.
 */
#[CoversClass(DashboardWidget::class)]
final class DashboardWidgetTest extends TestCase
{
    /**
     * The scale is a wire contract, so the boundaries are worth pinning:
     * loosening `COLUMNS_MAX` later re-lays-out every stored layout that named
     * the old maximum.
     */
    #[Test]
    #[TestWith([1])]
    #[TestWith([4])]
    #[TestWith([12])]
    public function aWidthWithinTheGridIsAccepted(int $columns): void
    {
        self::assertSame($columns, $this->widget($columns)->columns);
    }

    /**
     * Rejected rather than clamped. A clamp would hand back a card the module
     * never chose, at a width its author cannot see is wrong — the silent
     * no-op, where the loud failure is the cheap one.
     */
    #[Test]
    #[TestWith([0])]
    #[TestWith([-1])]
    #[TestWith([13])]
    #[TestWith([40])]
    public function aWidthTheGridCannotHoldIsRefused(int $columns): void
    {
        $this->expectException(InvalidArgumentException::class);
        // The id, because the module author reading this needs to know WHICH
        // widget — a stack trace through a tagged-iterator registry does not
        // say, and a dashboard collects widgets from every installed module.
        $this->expectExceptionMessageMatches('/vfs\.file-count/');

        $this->widget($columns);
    }

    /**
     * A widget that says nothing about its width gets a third of the row, which
     * is what the auto-filled grid already drew before the twelfths existed.
     * Widening the scale was meant to re-lay-out nothing, and a default
     * that moved would have done exactly that to the two shipped VFS cards.
     */
    #[Test]
    public function aWidgetThatDeclaresNoWidthTakesAThirdOfTheRow(): void
    {
        $widget = new DashboardWidget(
            id: 'vfs.file-count',
            label: 'Files stored',
            icon: 'bi-files',
            endpoint: '/api/v1/vfs/storage/stat',
            valuePath: 'data.database.fileCount',
        );

        self::assertSame(4, $widget->columns);
        self::assertSame(DashboardWidget::COLUMNS_DEFAULT, $widget->columns);
        self::assertSame(12, DashboardWidget::COLUMNS_MAX);
    }

    private function widget(int $columns): DashboardWidget
    {
        return new DashboardWidget(
            id: 'vfs.file-count',
            label: 'Files stored',
            icon: 'bi-files',
            endpoint: '/api/v1/vfs/storage/stat',
            valuePath: 'data.database.fileCount',
            columns: $columns,
        );
    }
}

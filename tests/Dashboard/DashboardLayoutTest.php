<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Dashboard;

use CoolMS\Core\Dashboard\DashboardLayout;
use CoolMS\Core\Dashboard\DashboardPlacement;
use CoolMS\Core\Dashboard\DashboardWidget;
use CoolMS\Core\Dashboard\PlacedWidget;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_map;

/**
 * What a saved layout may do to the catalogue, and what it may not.
 *
 * The rule under nearly every test here is that a layout is an OVERRIDE: it
 * says what to change and stays quiet about the rest. The failure that model
 * exists to prevent is silent and slow — install a module a year after someone
 * saved a layout, and an exhaustive layout would leave its widget nowhere, with
 * nothing logged and nothing to see.
 */
#[CoversClass(DashboardLayout::class)]
#[CoversClass(DashboardPlacement::class)]
final class DashboardLayoutTest extends TestCase
{
    #[Test]
    public function noLayoutLeavesTheCatalogueAlone(): void
    {
        $catalogue = [$this->widget('a'), $this->widget('b')];

        self::assertSame(['a', 'b'], $this->ids(DashboardLayout::none()->apply($catalogue)));
        self::assertTrue(DashboardLayout::none()->isEmpty());
    }

    #[Test]
    public function placementsDecideTheOrder(): void
    {
        $layout = new DashboardLayout([
            new DashboardPlacement('c'),
            new DashboardPlacement('a'),
        ]);

        self::assertSame(
            ['c', 'a', 'b'],
            $this->ids($layout->apply([$this->widget('a'), $this->widget('b'), $this->widget('c')])),
        );
    }

    /**
     * THE test for the override model. A module installed after the layout was
     * written contributes a widget nobody could have mentioned; it has to
     * appear anyway, or installing a module looks like installing nothing.
     */
    #[Test]
    public function aWidgetTheLayoutNeverMentionedIsAppendedRatherThanDropped(): void
    {
        $layout = new DashboardLayout([new DashboardPlacement('b')]);

        self::assertSame(
            ['b', 'a', 'newly-installed'],
            $this->ids($layout->apply([
                $this->widget('a'),
                $this->widget('b'),
                $this->widget('newly-installed'),
            ])),
        );
    }

    #[Test]
    public function aPlacementResizesTheCardItNames(): void
    {
        $layout = new DashboardLayout([new DashboardPlacement('a', columns: 12)]);

        $applied = $layout->apply([$this->widget('a', columns: 4), $this->widget('b', columns: 4)]);

        self::assertSame(12, $applied[0]->widget->columns);
        // Untouched: a layout that resizes one card must not restate the rest.
        self::assertSame(4, $applied[1]->widget->columns);
    }

    /**
     * A placement with no width keeps the module's, so a layout that only
     * REORDERS does not quietly freeze every card at the width it happened to
     * have the day it was saved.
     */
    #[Test]
    public function aPlacementWithoutAWidthKeepsTheModulesOwn(): void
    {
        $layout = new DashboardLayout([new DashboardPlacement('a')]);

        self::assertSame(6, $layout->apply([$this->widget('a', columns: 6)])[0]->widget->columns);
    }

    /**
     * MARKED, not removed — and kept where it was. A dashboard skips it;
     * an editor needs it, because the only cards anyone wants to add back are
     * the ones that are not being drawn. Dropping it here would have forced a
     * second endpoint returning the unfiltered catalogue.
     */
    #[Test]
    public function aHiddenPlacementIsMarkedAndKeepsItsPosition(): void
    {
        $layout = new DashboardLayout([
            new DashboardPlacement('a', hidden: true),
            new DashboardPlacement('b'),
        ]);

        $applied = $layout->apply([$this->widget('a'), $this->widget('b')]);

        self::assertSame(['a', 'b'], $this->ids($applied));
        self::assertTrue($applied[0]->hidden);
        self::assertFalse($applied[1]->hidden);
    }

    /** Nothing is hidden unless a layout says so. */
    #[Test]
    public function aWidgetTheLayoutNeverMentionedIsNotHidden(): void
    {
        self::assertFalse(DashboardLayout::none()->apply([$this->widget('a')])[0]->hidden);
    }

    /**
     * ⚠️ The effective width and the STATED width are different facts, and only
     * an editor can tell them apart from the outside. It re-submits the whole
     * layout on every save, so a card nobody touched must go back as "unstated"
     * — sending the number it happens to measure would convert a module's
     * default into a stored decision, permanently.
     */
    #[Test]
    public function theWidthTheLayoutSTATEDIsCarriedSeparatelyFromTheOneInForce(): void
    {
        $layout = new DashboardLayout([
            new DashboardPlacement('sized', columns: 12),
            new DashboardPlacement('untouched'),
        ]);

        $applied = $layout->apply([
            $this->widget('sized', columns: 4),
            $this->widget('untouched', columns: 4),
            $this->widget('unmentioned', columns: 4),
        ]);

        self::assertSame(12, $applied[0]->widget->columns);
        self::assertSame(12, $applied[0]->explicitColumns);

        // Placed, but with nothing said about its width.
        self::assertSame(4, $applied[1]->widget->columns);
        self::assertNull($applied[1]->explicitColumns);

        // Never mentioned at all.
        self::assertNull($applied[2]->explicitColumns);
    }

    /**
     * A layout outliving the module it mentions must not take the dashboard
     * with it. The placement is skipped, not refused — and deliberately not
     * pruned, so the position survives a module being reinstalled.
     */
    #[Test]
    public function aPlacementForAWidgetNobodyOffersIsIgnored(): void
    {
        $layout = new DashboardLayout([
            new DashboardPlacement('uninstalled.thing'),
            new DashboardPlacement('a'),
        ]);

        self::assertSame(['a'], $this->ids($layout->apply([$this->widget('a')])));
    }

    /**
     * ⚠️ A layout may only REORDER, RESIZE and HIDE. It cannot add a card, so
     * one written when the viewer had more roles — or edited by hand — can
     * never put back a widget the registry refused to offer.
     */
    #[Test]
    public function aLayoutCannotAddAWidgetTheCatalogueDidNotOffer(): void
    {
        $layout = new DashboardLayout([
            new DashboardPlacement('secrets.for-admins-only', columns: 12),
        ]);

        self::assertSame(['a'], $this->ids($layout->apply([$this->widget('a')])));
    }

    /** Drawing one card twice is the alternative; the registry's rule, reused. */
    #[Test]
    public function aWidgetPlacedTwiceIsDrawnOnceAtTheFirstPosition(): void
    {
        $layout = new DashboardLayout([
            new DashboardPlacement('a', columns: 3),
            new DashboardPlacement('b'),
            new DashboardPlacement('a', columns: 12),
        ]);

        $applied = $layout->apply([$this->widget('a'), $this->widget('b')]);

        self::assertSame(['a', 'b'], $this->ids($applied));
        self::assertSame(3, $applied[0]->widget->columns);
    }

    /** A human edits this file, so it is the likelier source of a bad width. */
    #[Test]
    public function aPlacementWidthOutsideTheGridIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/some\.widget/');

        new DashboardPlacement('some.widget', columns: 13);
    }

    /**
     * @param list<PlacedWidget> $placed
     *
     * @return list<string>
     */
    private function ids(array $placed): array
    {
        return array_map(static fn (PlacedWidget $p): string => $p->widget->id, $placed);
    }

    private function widget(string $id, int $columns = DashboardWidget::COLUMNS_DEFAULT): DashboardWidget
    {
        return new DashboardWidget(
            id: $id,
            label: 'Widget ' . $id,
            icon: 'bi-graph-up',
            endpoint: '/api/v1/' . $id,
            valuePath: 'count',
            columns: $columns,
        );
    }
}

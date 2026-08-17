<?php

declare(strict_types=1);

namespace CoolMS\Core\Dashboard;

use function array_key_exists;

/**
 * A saved arrangement of dashboard cards, applied over the catalogue.
 *
 * ## An OVERRIDE, never a replacement — and that is the whole design
 *
 * The obvious model is "the layout lists the dashboard". It is wrong, and
 * wrong in a way that only shows up months later: install a module, and its
 * widget is nowhere, because a file written before that module existed does not
 * mention it. Nothing errors, nothing logs, and the module looks broken.
 *
 * So a layout says only what it wants to CHANGE. Widgets it names are placed in
 * its order at its widths; every other widget the catalogue offers is appended
 * in the order its module offered it. A new module's card therefore appears on
 * its own, at the end, and a layout stays valid across installs.
 *
 * That is the same relationship every other file under `config/modules` has
 * with the contributors it sits above: config DATA overriding what modules
 * provide, not standing in for it.
 *
 * ## Nothing here is trusted
 *
 * The catalogue has already dropped widgets this viewer may not be offered, and
 * this only ever REORDERS, RESIZES and HIDES what it is given. A layout cannot
 * add a widget, so an edited file — or one written when the viewer had more
 * roles — can never put back a card the registry refused.
 */
final readonly class DashboardLayout
{
    /** @param list<DashboardPlacement> $placements in the order they should appear */
    public function __construct(
        public array $placements = [],
    ) {
    }

    /** No layout saved: the catalogue's own order stands, untouched. */
    public static function none(): self
    {
        return new self();
    }

    public function isEmpty(): bool
    {
        return [] === $this->placements;
    }

    /**
     * The catalogue as this layout arranges it.
     *
     * Hidden widgets are MARKED and kept in place rather than removed:
     * a dashboard skips them, and an editor needs them — the only cards anyone
     * wants to add back are the ones that are not being drawn.
     *
     * @param list<DashboardWidget> $catalogue as the registry offered it
     *
     * @return list<PlacedWidget>
     */
    public function apply(array $catalogue): array
    {
        $available = [];
        foreach ($catalogue as $widget) {
            $available[$widget->id] = $widget;
        }

        $out = [];
        $placed = [];
        foreach ($this->placements as $placement) {
            // A placement for a widget that is not on offer — the module was
            // uninstalled, or the registry filtered it out for this viewer.
            // Ignored rather than refused: a layout outliving one module must
            // not take the whole dashboard down with it, and re-saving would
            // silently discard the position in case the module comes back.
            if (!array_key_exists($placement->widget, $available)) {
                continue;
            }
            // A widget named twice would otherwise be drawn twice. First wins,
            // matching the registry's rule for duplicate ids.
            if (array_key_exists($placement->widget, $placed)) {
                continue;
            }

            $placed[$placement->widget] = true;

            $widget = $available[$placement->widget];
            $out[] = new PlacedWidget(
                null === $placement->columns ? $widget : $widget->withColumns($placement->columns),
                $placement->hidden,
                $placement->columns,
            );
        }

        // Everything the layout never mentioned, in catalogue order. This is
        // what makes a newly installed module's widget appear without anyone
        // editing anything.
        foreach ($catalogue as $widget) {
            if (!array_key_exists($widget->id, $placed)) {
                $out[] = new PlacedWidget($widget);
            }
        }

        return $out;
    }
}

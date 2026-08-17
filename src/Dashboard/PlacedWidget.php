<?php

declare(strict_types=1);

namespace CoolMS\Core\Dashboard;

/**
 * A widget together with what the saved layout decided about it.
 *
 * ## Why `hidden` travels instead of the card simply vanishing
 *
 * Until now {@see DashboardLayout::apply()} dropped hidden widgets, which is
 * right for DRAWING a dashboard and useless for ARRANGING one: an editor that
 * cannot see what is hidden cannot offer to put it back, and the only cards a
 * user wants to add are exactly the ones that are not there.
 *
 * The alternative was a second endpoint returning the unfiltered catalogue —
 * two routes that must agree about the same list, which is one more than can be
 * kept honest. So one route answers "everything you may see, in order, with the
 * hidden ones marked", and the page skips what it should not draw. The renderer
 * loses nothing: skipping a flagged item is the same work as receiving a shorter
 * list.
 *
 * Nothing about permissions changes. The registry has already removed widgets
 * this viewer may not be offered, so a hidden entry here is one the viewer chose
 * to hide — never one they were not allowed to see.
 */
final readonly class PlacedWidget
{
    public function __construct(
        public DashboardWidget $widget,
        /** Hidden by the layout: keeps its position, is not drawn. */
        public bool $hidden = false,
        /**
         * The width the LAYOUT set, or null when it did not say.
         *
         * `$widget->columns` cannot answer this: it is the effective width, and
         * a 4 there may be the module's own choice or a saved override. An
         * editor has to tell them apart, because it re-submits the whole layout
         * on every save — so a card nobody touched must go back as "unstated",
         * not as the number it currently happens to measure. Sending the number
         * would silently convert every module default into a decision, and the
         * first module to improve its own card would be overruled by a layout
         * nobody edited.
         */
        public ?int $explicitColumns = null,
    ) {
    }
}

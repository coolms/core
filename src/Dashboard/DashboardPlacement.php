<?php

declare(strict_types=1);

namespace CoolMS\Core\Dashboard;

use InvalidArgumentException;

use function sprintf;

/**
 * One line of a dashboard layout: where a widget goes and how wide.
 *
 * ## Why a placement is not just an id and a width
 *
 * A layout is an OVERRIDE of the catalogue, not a replacement for it — the same
 * relationship every other `config/modules` file has with the contributors it
 * sits above. That makes each field optional in its own way:
 *
 *  - `columns` null keeps whatever the module asked for. A layout that only
 *    re-ORDERS should not have to restate every width, and one that restates
 *    them freezes each card at the width it happened to have the day the layout
 *    was saved — so a module that later improves its own card is overruled by a
 *    file nobody meant as a decision.
 *  - `hidden` removes a card while KEEPING its position, so putting it back is
 *    a toggle rather than a re-drag. It lives here rather than in a separate
 *    list of ids for exactly that reason.
 */
final readonly class DashboardPlacement
{
    /**
     * @param string   $widget  id of the {@see DashboardWidget} being placed. A
     *                          layout naming a widget no installed module
     *                          offers is IGNORED rather than an error — see
     *                          {@see DashboardLayout::apply()}
     * @param int|null $columns width in twelfths, overriding the module's own;
     *                          null keeps it
     * @param bool     $hidden  drop this card while remembering where it was
     */
    public function __construct(
        public string $widget,
        public ?int $columns = null,
        public bool $hidden = false,
    ) {
        /*
         * The same range the widget itself enforces, and enforced here for the
         * same reason: a width out of the grid is wrong on its own, without
         * needing to know anything else. A stored layout is the likelier source
         * of one, since a human edits it.
         */
        if (null !== $columns && ($columns < DashboardWidget::COLUMNS_MIN || $columns > DashboardWidget::COLUMNS_MAX)) {
            throw new InvalidArgumentException(sprintf('Dashboard layout places widget "%s" at %d columns; the grid has %d (%d-%d).', $widget, $columns, DashboardWidget::COLUMNS_MAX, DashboardWidget::COLUMNS_MIN, DashboardWidget::COLUMNS_MAX));
        }
    }
}

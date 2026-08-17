<?php

declare(strict_types=1);

namespace CoolMS\Core\Dashboard;

use InvalidArgumentException;

use function sprintf;

/**
 * One thing a module offers to put on a dashboard.
 *
 * ## A CATALOGUE entry, not a renderer
 *
 * The widget describes itself and says where its data comes from; it does not
 * hand back markup or a number. That is the same trade {@see
 * the VFS creatable-file-kind makes for the new-file menu, and
 * it is the load-bearing decision here:
 *
 *  - **The widget owns its query.** A module that knows how to count its own
 *    documents keeps that knowledge, behind its own endpoint, with its own
 *    filters and its own caching. A dashboard that fetched counts on the
 *    module's behalf would need to know every module's storage.
 *  - **The widget owns its permissions.** `endpoint` is a normal API route
 *    with normal security, so a viewer who cannot read a module gets the 403
 *    they would get anywhere else. A server-rendered number would have to
 *    re-derive that here, and the first missed case leaks a count of something
 *    the viewer cannot see — exactly the trap this shape avoids.
 *  - **The widget owns its refresh.** A live figure and a nightly one differ
 *    only in how often the client asks.
 *
 * The dashboard therefore owns exactly one thing: PLACEMENT.
 *
 * ## Why `requiredRole` as well, when the endpoint already guards
 *
 * Because an empty tile is worse than an absent one. The endpoint decides
 * whether the DATA may be read; this decides whether the widget is worth
 * offering at all, so a viewer without the role never sees a card that can only
 * ever show an error. It is a display filter and nothing is trusted to it — the
 * endpoint remains the authority, which is why it is stated separately rather
 * than being derived from one.
 */
final readonly class DashboardWidget
{
    /**
     * ## Why a path into an EXISTING response, not a response shape of ours.
     *
     * The first design mandated what a widget's endpoint must return. Then a
     * real one was looked at: `GET /vfs/storage/stat` already answers
     * `{totalSize, fileCount, humanSize, storagePath}`, and VFS has four more
     * like it. A required shape would have meant a NEW route per widget,
     * serving data the module already serves, and every module paying that toll
     * before it could contribute anything.
     *
     * So the widget adapts to its module instead. That is the same principle
     * one level down: the widget owns its query, and a query it cannot reuse is
     * not really its own. A module with no suitable endpoint is free to add
     * one; nothing is forced on a module that already had it.
     */
    public const string PATH_SEPARATOR = '.';

    /**
     * ## The grid is TWELVE columns wide, and that number is a promise.
     *
     * A width has to be expressed against something. "How many cards fit in a
     * row" cannot be it — that answer changes with the viewport, so a widget
     * declaring `2` would mean a different width on every screen and no module
     * could reason about its own card. Twelfths do not move: a `4` is a third
     * of the dashboard everywhere, and the client decides only how a third
     * behaves when there is no room for one.
     *
     * Twelve because it divides by 2, 3, 4 and 6 — halves, thirds, quarters and
     * sixths all land on whole columns, which is why the same number underpins
     * every grid system the admin's users have already met.
     *
     * ⚠️ Widened from 1-4 BEFORE any module outside VFS contributes.
     * The scale is the wire contract: a `2` meaning "half" today and "a sixth"
     * tomorrow silently re-lays-out every dashboard that stored one. Free to
     * change while one module contributes; a migration once several do.
     */
    public const int COLUMNS_MIN = 1;

    public const int COLUMNS_MAX = 12;

    /**
     * A third of the row, which is what the first cut ALREADY drew.
     *
     * Before the grid was fixed the page auto-filled `minmax(220px, 1fr)` and
     * fitted three cards across the admin's content pane. Four twelfths keeps
     * exactly that, so widening the scale re-lays-out nothing — and it stays
     * the right side of the 220px the auto-fill was tuned for, which a quarter
     * (182px in that pane) would not.
     */
    public const int COLUMNS_DEFAULT = 4;

    /** A single number with a caption — the cheapest useful widget. */
    public const string KIND_STAT = 'stat';

    /** A series over time. */
    public const string KIND_CHART = 'chart';

    /** A short list of recent things. */
    public const string KIND_LIST = 'list';

    /**
     * What a client is guaranteed to be able to draw — TODAY, which is why it
     * is shorter than the constants above.
     *
     * A widget kind is a CONTRACT with the admin's renderer, not a free-form
     * hint: a module inventing `sparkline` would produce a card nothing knows
     * how to draw, and an unknown kind is better refused at the seam than
     * rendered as a stat meaning something else.
     *
     * `chart` and `list` are named as constants because they are the intended
     * vocabulary, and deliberately NOT listed here until the admin can draw
     * them. Listing a kind the client cannot render would make this test
     * meaningless — the point is that it refuses undrawable cards, and it can
     * only do that while it describes what is actually drawable. Widening it is
     * one line, at the same moment the renderer gains the case.
     */
    public const array KINDS = [self::KIND_STAT];

    /**
     * @param string      $id           stable, module-prefixed — `document.count`.
     *                                  The client stores layout against it, so it
     *                                  outlives labels and translations
     * @param string      $label        shown on the card; a translation KEY is
     *                                  fine, the admin resolves it
     * @param string      $icon         bootstrap-icons class, e.g. `bi-file-text`
     * @param string      $endpoint     API path the client GETs for this widget's
     *                                  own data. Its security is the real gate
     * @param string      $kind         how to draw what comes back — see
     *                                  {@see self::KINDS}. Unknown kinds are
     *                                  dropped by the registry rather than
     *                                  rendered as something they are not
     * @param string      $valuePath    dot-path to the figure inside whatever the
     *                                  endpoint already returns — `fileCount`,
     *                                  `stats.total`. See the note below on why
     *                                  this exists instead of a mandated
     *                                  response shape
     * @param string|null $displayPath  dot-path to a PRE-FORMATTED string to show
     *                                  instead of the raw figure — VFS already
     *                                  returns `humanSize` beside `totalSize`,
     *                                  and the module that owns the number knows
     *                                  best how to write it. Null shows the value
     * @param int         $columns      how much of the dashboard's TWELVE-column
     *                                  grid the card wants — see
     *                                  {@see self::COLUMNS_MAX}
     * @param string|null $requiredRole hides the card from viewers without it;
     *                                  null offers it to anyone signed in
     * @param string|null $group        section this widget belongs to, so the
     *                                  same catalogue can feed a section page
     *                                  later; null means the main dashboard
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $icon,
        public string $endpoint,
        public string $valuePath,
        public ?string $displayPath = null,
        public string $kind = self::KIND_STAT,
        public int $columns = self::COLUMNS_DEFAULT,
        public ?string $requiredRole = null,
        public ?string $group = null,
    ) {
        /*
         * ## Why this throws where the registry merely SKIPS
         *
         * The registry's refusals — a duplicate id, an unknown kind, a missing
         * role — all need context this object does not have: the other widgets,
         * and the viewer. A width does not. It is wrong on its own, in one
         * module's own code, and the module author is the only person who can
         * fix it.
         *
         * The registry could not catch it anyway: construction happens inside
         * the provider, upstream of anything the registry sees. So the choice
         * is really "throw here" or "let a span-40 card tear the grid apart on
         * someone else's screen", and a loud failure the first time the
         * provider runs is the kinder of the two.
         *
         * Deliberately NOT clamped. A silently narrowed card is a module
         * shipping a layout it never chose and cannot see is wrong.
         */
        if ($columns < self::COLUMNS_MIN || $columns > self::COLUMNS_MAX) {
            throw new InvalidArgumentException(sprintf('Dashboard widget "%s" asked for %d columns; the dashboard grid has %d (%d-%d).', $id, $columns, self::COLUMNS_MAX, self::COLUMNS_MIN, self::COLUMNS_MAX));
        }
    }

    /**
     * The same widget at a width a saved layout chose instead of the module.
     *
     * The only field a layout may change, and the reason is the seam: a layout
     * decides PLACEMENT, so it may say how much room a card gets and nothing
     * else. Where its data comes from, what it is called and who may see it
     * stay the module's to answer — a layout that could rewrite `endpoint` or
     * `requiredRole` would be a config file quietly overruling a module's
     * security.
     */
    public function withColumns(int $columns): self
    {
        return new self(
            id: $this->id,
            label: $this->label,
            icon: $this->icon,
            endpoint: $this->endpoint,
            valuePath: $this->valuePath,
            displayPath: $this->displayPath,
            kind: $this->kind,
            columns: $columns,
            requiredRole: $this->requiredRole,
            group: $this->group,
        );
    }
}

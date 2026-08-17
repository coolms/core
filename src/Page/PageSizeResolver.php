<?php

declare(strict_types=1);

namespace CoolMS\Core\Page;

use function is_int;
use function is_numeric;
use function is_string;

/**
 * Reads a page's `extras` (the document-level `pageSize` + optional custom
 * `pageWidth`) and resolves them for each render surface, against the single
 * {@see PageSize} catalog. A pure, stateless value service — no I/O, no DI deps
 * — so the Web SSR contributor and the DOCX renderer (and a unit test) all share
 * one resolution path and can never diverge.
 *
 * Every method degrades to "unset" (null) for an absent or unrecognised
 * `pageSize`, so a page that never opted in renders exactly as before.
 */
final readonly class PageSizeResolver
{
    /** Extras key holding the selected preset value (a {@see PageSize}). */
    public const string SIZE_KEY = 'pageSize';

    /** Extras key holding the custom pixel width (only read for `custom`). */
    public const string WIDTH_KEY = 'pageWidth';

    /**
     * Extras key holding a {@see PageOrientation}, applied on top of whichever
     * size is selected. Absent means "however the size declares
     * itself", so nothing that never opted in changes.
     */
    public const string ORIENTATION_KEY = 'pageOrientation';

    /**
     * The CSS `max-width` for the content area, or null to keep the theme's
     * responsive default.
     *
     * @param array<string, mixed> $extras the page Package's `extras`
     */
    public function webMaxWidth(array $extras): ?string
    {
        return $this->size($extras)?->webMaxWidth($this->customWidth($extras));
    }

    /**
     * PHPWord section settings, or null to keep PHPWord's default page.
     *
     * @param array<string, mixed> $extras the document/template Node's `extras`
     *
     * @return array{pageSizeW: int, pageSizeH: int, orientation: string}|null
     */
    public function docxSection(array $extras): ?array
    {
        $section = $this->size($extras)?->docxSection();
        if (null === $section) {
            return null;
        }

        // Orientation is applied ON TOP of the size, so A3/Letter/Legal
        // can be landscape too — `wide` used to be the only landscape option
        // because it fused the two axes.
        return $this->orientation($extras)?->applyTo($section) ?? $section;
    }

    /**
     * The sheet dimensions for a PAGED editor canvas, as CSS lengths, or null
     * when the size maps to no printed page.
     *
     * Derived from {@see docxSection()} — the very array the DOCX renderer
     * hands PHPWord — rather than from a parallel millimetre table. That is the
     * whole point: orientation has already been applied there, so the sheet on
     * screen and the paper in the .docx cannot drift, and adding a size to the
     * catalog cannot leave the canvas behind.
     *
     * Not to be confused with {@see webMaxWidth()}: that is how wide the
     * content column is on the SITE, a responsive-layout concern with no
     * height. This is paper.
     *
     * @param array<string, mixed> $extras the document/template Node's `extras`
     *
     * @return array{width: string, height: string}|null
     */
    public function sheetGeometry(array $extras): ?array
    {
        $section = $this->docxSection($extras);
        if (null === $section) {
            return null;
        }

        return [
            'width' => self::twipsToCss($section['pageSizeW']),
            'height' => self::twipsToCss($section['pageSizeH']),
        ];
    }

    /**
     * The chosen orientation, or null when the document never set one (in
     * which case the size's own declaration stands).
     *
     * @param array<string, mixed> $extras
     */
    public function orientation(array $extras): ?PageOrientation
    {
        $raw = $extras[self::ORIENTATION_KEY] ?? null;

        return is_string($raw) ? PageOrientation::tryFrom($raw) : null;
    }

    /**
     * Orientation choices for an admin select, in catalog order.
     *
     * @return list<array{value: string, label: string}>
     */
    public function orientationOptions(): array
    {
        return array_map(
            static fn (PageOrientation $o): array => ['value' => $o->value, 'label' => $o->label()],
            PageOrientation::cases(),
        );
    }

    /**
     * The preset list for an admin select: `[{value, label}, …]` in catalog
     * order. The backend owns the option set so the FE control never hardcodes
     * one.
     *
     * @return list<array{value: string, label: string}>
     */
    public function options(): array
    {
        return array_map(
            static fn (PageSize $s): array => ['value' => $s->value, 'label' => $s->label()],
            PageSize::cases(),
        );
    }

    /**
     * The DOCX-meaningful subset of {@see options()}: only presets that map to
     * a real printed page (a non-null {@see PageSize::docxSection()} — A4 /
     * Letter / Legal / Wide). The document-builder offers these, since `full`
     * and `custom` are web-display widths that leave the DOCX page at PHPWord's
     * default and so would be no-op choices there. Derived from the catalog
     * (never a hardcoded list) so it can never drift from the render side.
     *
     * @return list<array{value: string, label: string}>
     */
    public function docxOptions(): array
    {
        return array_values(array_map(
            static fn (PageSize $s): array => ['value' => $s->value, 'label' => $s->label()],
            array_filter(PageSize::cases(), static fn (PageSize $s): bool => null !== $s->docxSection()),
        ));
    }

    /**
     * Twips back to a CSS millimetre length, rounded to 0.1mm.
     *
     * The round-trip through twips loses nothing an author can see — A4's
     * 11906 twips comes back as 210.0004mm — but printing that verbatim into
     * CSS is noise, and the trailing `.0` on whole millimetres reads like a
     * measurement precision we do not have.
     */
    private static function twipsToCss(int $twips): string
    {
        $mm = round($twips / PageSize::TWIPS_PER_MM, 1);

        return rtrim(rtrim(number_format($mm, 1, '.', ''), '0'), '.') . 'mm';
    }

    /** @param array<string, mixed> $extras */
    private function size(array $extras): ?PageSize
    {
        $raw = $extras[self::SIZE_KEY] ?? null;

        return is_string($raw) ? PageSize::tryFrom($raw) : null;
    }

    /** @param array<string, mixed> $extras */
    private function customWidth(array $extras): ?int
    {
        $raw = $extras[self::WIDTH_KEY] ?? null;
        if (is_int($raw)) {
            return $raw;
        }

        return is_numeric($raw) ? (int) $raw : null;
    }
}

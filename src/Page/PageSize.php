<?php

declare(strict_types=1);

namespace CoolMS\Core\Page;

/**
 * The per-document page-size presets (Track B — page-size / docx-width control).
 *
 * A page (a VFS Package) opts into one of these via `extras.pageSize`; absent
 * means "unset", and every render surface keeps its current default behaviour
 * (responsive `.container` on the web, PHPWord's default page on docx). The
 * single source of truth shared by all surfaces (web SSR, DOCX render, the admin
 * editor's option list) so they never drift — the web max-width, the DOCX paper
 * dimensions, and the FE select all derive from here.
 *
 * `paper` sizes (A4 / Letter / Legal) carry real DOCX dimensions; `wide` is a
 * landscape variant; `full` and `custom` are web-display concepts that leave the
 * DOCX page at its default (a free pixel width has no honest paper mapping).
 */
enum PageSize: string
{
    case A4 = 'a4';
    /**
     * Added for the Word-look editor. Deliberately a SIZE only —
     * orientation is a separate axis now ({@see PageOrientation}), because
     * `wide` conflated the two and made "A3 landscape" unexpressible.
     */
    case A3 = 'a3';
    case Letter = 'letter';
    case Legal = 'legal';
    case Wide = 'wide';
    case Full = 'full';
    case Custom = 'custom';

    /**
     * Twips per millimetre (1440 twips/inch ÷ 25.4 mm/inch).
     *
     * Public because the paged editor canvas converts BACK from the
     * twips {@see docxSection()} produced, rather than keeping a second table
     * of millimetres. One table means the sheet an author sees cannot disagree
     * with the paper the renderer sets — and a second table is exactly the kind
     * of thing that stays right until someone adds a size to one of them.
     */
    public const float TWIPS_PER_MM = 56.692_913_385_8;

    public function label(): string
    {
        return match ($this) {
            self::A4 => 'A4',
            self::A3 => 'A3',
            self::Letter => 'Letter',
            self::Legal => 'Legal',
            self::Wide => 'Wide',
            self::Full => 'Full width',
            self::Custom => 'Custom',
        };
    }

    /**
     * The CSS `max-width` for the content area, or null to keep the theme's
     * responsive default. `custom` needs the author's pixel width passed in.
     */
    public function webMaxWidth(?int $customPx = null): ?string
    {
        return match ($this) {
            self::A4 => '210mm',
            self::A3 => '297mm',
            self::Letter, self::Legal => '8.5in',
            self::Wide => '1320px',
            self::Full => '100%',
            self::Custom => null !== $customPx && $customPx > 0 ? $customPx . 'px' : null,
        };
    }

    /**
     * PHPWord section settings (page dimensions in twips + orientation), or null
     * to leave the DOCX page at PHPWord's default. `full`/`custom` return null —
     * a web-only width does not change the printed paper.
     *
     * @return array{pageSizeW: int, pageSizeH: int, orientation: string}|null
     */
    public function docxSection(): ?array
    {
        return match ($this) {
            self::A4 => self::portrait(210.0, 297.0),
            self::A3 => self::portrait(297.0, 420.0),
            self::Letter => self::inchesPortrait(8.5, 11.0),
            self::Legal => self::inchesPortrait(8.5, 14.0),
            // Wide == A4 in landscape (swap W/H, mark orientation).
            self::Wide => [
                'pageSizeW' => self::mmToTwips(297.0),
                'pageSizeH' => self::mmToTwips(210.0),
                'orientation' => 'landscape',
            ],
            self::Full, self::Custom => null,
        };
    }

    /** @return array{pageSizeW: int, pageSizeH: int, orientation: string} */
    private static function portrait(float $widthMm, float $heightMm): array
    {
        return [
            'pageSizeW' => self::mmToTwips($widthMm),
            'pageSizeH' => self::mmToTwips($heightMm),
            'orientation' => 'portrait',
        ];
    }

    /** @return array{pageSizeW: int, pageSizeH: int, orientation: string} */
    private static function inchesPortrait(float $widthIn, float $heightIn): array
    {
        return self::portrait($widthIn * 25.4, $heightIn * 25.4);
    }

    private static function mmToTwips(float $mm): int
    {
        return (int) round($mm * self::TWIPS_PER_MM);
    }
}

<?php

declare(strict_types=1);

namespace CoolMS\Core\Page;

/**
 * Page orientation, an axis SEPARATE from {@see PageSize}.
 *
 * The catalog used to carry `wide`, which is A4-in-landscape — size and
 * orientation fused into one preset. That works for exactly one combination
 * and makes every other one unexpressible: there was no way to say A3
 * landscape, or Letter landscape, because the preset list would have to grow a
 * case per pairing.
 *
 * Held in the page/template `extras` under {@see PageSizeResolver::ORIENTATION_KEY}
 * and applied ON TOP of whichever size is chosen, so N sizes x 2 orientations
 * cost N + 2 cases instead of 2N.
 *
 * `wide` stays in the catalog and keeps working: it is persisted in existing
 * documents, and re-spelling it as "a4 + landscape" would silently rewrite
 * data. It simply becomes the one preset that carries its own orientation.
 */
enum PageOrientation: string
{
    case Portrait = 'portrait';
    case Landscape = 'landscape';

    public function label(): string
    {
        return match ($this) {
            self::Portrait => 'Portrait',
            self::Landscape => 'Landscape',
        };
    }

    /**
     * Apply this orientation to a PHPWord section, swapping the page
     * dimensions when it disagrees with how the size declared itself.
     *
     * PHPWord wants BOTH the swapped `pageSizeW`/`pageSizeH` and the
     * `orientation` marker — setting the marker alone leaves the paper portrait
     * and produces a landscape-labelled document with portrait dimensions.
     *
     * @param array{pageSizeW: int, pageSizeH: int, orientation: string} $section
     *
     * @return array{pageSizeW: int, pageSizeH: int, orientation: string}
     */
    public function applyTo(array $section): array
    {
        if ($section['orientation'] === $this->value) {
            return $section;
        }

        return [
            'pageSizeW' => $section['pageSizeH'],
            'pageSizeH' => $section['pageSizeW'],
            'orientation' => $this->value,
        ];
    }
}

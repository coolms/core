<?php

declare(strict_types=1);
namespace CoolMS\Core\Word;

use function preg_match;
use function strtolower;

/**
 * Where a paragraph's lines sit between the margins.
 *
 * ## The value is the CSS spelling, not OOXML's
 *
 * Three of the four agree; `justify` does not. OOXML's `w:jc` calls it
 * **`both`** -- justified on both edges -- which is the older typesetting name
 * for the same thing. The enum takes the CSS spelling because that is what the
 * `.ddoc` file, the editor HTML and the browser all use, and the ONE place that
 * needs the other name asks {@see self::ooxml()} for it.
 *
 * The alternative -- storing `both` and translating everywhere else -- would
 * put OOXML's vocabulary in the format, the editor and the FE, to save one
 * method here.
 *
 * ## !! Absent is not `Left`
 *
 * A paragraph with no alignment INHERITS: from its style, and failing that from
 * the document's default, which is left in a left-to-right document and right
 * in a right-to-left one. Writing `w:jc w:val="left"` onto every paragraph
 * would pin all of them to the left and quietly break the first Arabic or
 * Hebrew document anybody opens. So `Paragraph::$alignment` is nullable and
 * null writes nothing at all.
 */
enum ParagraphAlignment: string
{
    case Left = 'left';
    case Center = 'center';
    case Right = 'right';
    case Justify = 'justify';

    /**
     * The alignment a `style` attribute states, or null where it states none.
     *
     * Here rather than in each mapper because the enum's VALUES are already the
     * CSS keywords -- reading them back is the same vocabulary, not a second
     * one -- and two mappers plus the editor writer would otherwise carry three
     * copies of the same regex.
     */
    public static function fromStyle(string $style): ?self
    {
        if (1 !== preg_match('/(?:^|;)\s*text-align\s*:\s*([a-z-]+)/i', $style, $m)) {
            return null;
        }

        return self::tryFrom(strtolower($m[1]));
    }

    /**
     * The alignment a `w:jc w:val` means, or null for one we do not model.
     *
     * !! `start` and `end` are the DIRECTION-RELATIVE values a modern producer
     * writes, and they are not synonyms for left and right -- they resolve
     * against the paragraph's own direction. Mapping them onto left/right would
     * be correct for most documents and wrong for exactly the ones the
     * distinction exists for, so they are not modelled and stay unaligned
     * rather than being guessed at.
     */
    public static function fromOoxml(string $value): ?self
    {
        return match ($value) {
            'both', 'distribute' => self::Justify,
            'left' => self::Left,
            'center' => self::Center,
            'right' => self::Right,
            default => null,
        };
    }

    /** The `w:jc w:val` this alignment is written as. */
    public function ooxml(): string
    {
        return match ($this) {
            self::Justify => 'both',
            default => $this->value,
        };
    }
}

<?php

declare(strict_types=1);
namespace CoolMS\Core\Word;

/**
 * Whether a list counts or merely marks.
 *
 * ## Why this exists as a real distinction
 *
 * It did not survive the library this model replaces. Measured in
 * `tools/probe-phpword-lists.php`: PHPWord's `addListItem()` takes a style NAME
 * as its fourth argument, so the `TYPE_NUMBER` constant the `<ol>` mapper hands
 * it is looked up as a name, misses, and falls back -- every list in the
 * package, ordered or not, ends up pointing at a single `numId` whose every
 * level is `numFmt="bullet"`.
 *
 * So an authored numbered list has never printed numbers in a generated
 * `.docx`. Two cases here, and a separate numbering definition written for
 * each, is what makes the mapper's choice reach the reader.
 */
enum ListKind
{
    case Bullet;

    case Decimal;
}

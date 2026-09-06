<?php

declare(strict_types=1);
namespace CoolMS\Core\Word;

/** What a `w:br` interrupts: the line, or the page. */
enum BreakKind
{
    case Line;

    case Page;
}

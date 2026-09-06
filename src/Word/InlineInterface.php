<?php

declare(strict_types=1);
namespace CoolMS\Core\Word;

/**
 * Something that sits INSIDE a paragraph: a run, a hyperlink, a break, a
 * picture.
 *
 * The counterpart to {@see BlockInterface}, and the reason a paragraph can
 * accept a hyperlink without accepting a table.
 */
interface InlineInterface
{
}

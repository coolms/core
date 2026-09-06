<?php

declare(strict_types=1);
namespace CoolMS\Core\Word;

/**
 * Something that sits at BLOCK level: a paragraph or a table.
 *
 * WordprocessingML draws the line in the same place -- `w:body` and `w:tc` both
 * accept `w:p` and `w:tbl` and nothing else -- so a marker interface here is not
 * ceremony, it is the schema's own content model made checkable.
 */
interface BlockInterface
{
}

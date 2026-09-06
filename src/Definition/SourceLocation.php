<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

/**
 * Parse-time source location for an AST node.
 *
 * All AST nodes accept an optional {@see SourceLocation} so the
 * validator can quote precise positions in
 * {@see \CoolMS\Core\Definition\Violation::$path} /
 * `$message`. The parser stamps these when it can derive line +
 * column from the input; for the JSON parser the stamp is
 * NULL everywhere (the JSON tokeniser sidecar that produces real
 * line numbers is deferred per design Q3 in
 * `docs/investigations/m2c-design.md`).
 *
 * Shared between every module that ships a parser (Workflow today;
 * Decision DMN, Form Builder, a future BPMN XML parser).
 * Promoted to the Definition foundation so each parser doesn't ship
 * its own subtly-different shape.
 */
final readonly class SourceLocation
{
    public function __construct(
        public int $line,
        public int $column,
        public ?string $filePath = null,
    ) {
    }
}

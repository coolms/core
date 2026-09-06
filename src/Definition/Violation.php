<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

/**
 * Immutable record of a single validation finding emitted by a
 * deploy-time validation rule.
 *
 * Designed for accumulation, not for short-circuiting -- the
 * orchestrator collects every Violation in one pass so the author
 * sees every problem per round-trip.
 *
 * Field contract:
 *  - `$code` is stable and doc-linked (e.g. `'WF.UNSUPPORTED_CONSTRUCT'`,
 *    `'WF.G7_INVALID_BOUNDARY_HOST'`, future `'DMN.NO_INPUT'`...).
 *    Frontends localise via the code; per-module catalogues live in
 *    `docs/{module}/validation-codes.md`.
 *  - `$path` is a dotted / bracketed locator into the source AST
 *    (e.g. `'elements[7].boundaryEvents[0]'`,
 *    `'process.variables[userId].type'`,
 *    `'rules[3].inputs[2].expression'`). Authors paste it into their
 *    editor to jump to the offender.
 *  - `$message` is a deterministic English sentence -- the FE may
 *    localise but the wire copy is the canonical one.
 *  - `$severity` defaults to {@see Severity::Error} so rule authors
 *    must opt in to {@see Severity::Warning} (LCAP-safe default).
 *  - `$context` carries the structured payload (offending value,
 *    expected enum cases, the related element id, etc.) for
 *    machine-readable cockpit rendering. Keys are rule-local; no
 *    cross-rule schema enforced.
 *
 * Shared across every module that consumes the Definition foundation.
 */
final readonly class Violation
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $code,
        public string $path,
        public string $message,
        public Severity $severity = Severity::Error,
        public array $context = [],
    ) {
    }
}

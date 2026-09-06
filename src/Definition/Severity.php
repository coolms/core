<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

/**
 * Severity axis for a {@see Violation}.
 *
 * Plain (non-backed) enum -- the two cases never round-trip through
 * persistence or the wire as raw scalars. The wire shape uses the
 * lowercased name (`"error"` / `"warning"`); a presenter handles
 * that downstream.
 *
 * Only `Error` blocks deploy. `Warning` rides along inside the
 * {@see Violations} collection and is surfaced via the
 * `DefinitionDeployed` in the consuming application event payload
 * for cockpit display.
 *
 * Shared across every module that consumes the Definition foundation
 * (Workflow today; Decision/Form Builder in M3+).
 */
enum Severity
{
    case Error;
    case Warning;
}

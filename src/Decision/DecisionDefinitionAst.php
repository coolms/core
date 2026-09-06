<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use CoolMS\Core\Decision\DecisionTableAst;

/**
 * Root AST node for a DMN 1.3 decision (M3.1.c). Immutable.
 *
 * Single-decision shape only -- the M3.1 ship scope per the locked
 * decisions defers full DRD (Decision Requirements Diagram, multiple
 * linked decisions with `<informationRequirement>` cross-references)
 * to M4. When DRD lands this class will be one node in a graph keyed
 * by decision id.
 *
 * **M3.1.c scope: decision tables only.** Other decision-body shapes
 * the DMN 1.3 spec allows -- literal expressions, function
 * definitions, invocations, contexts, relations, lists -- are
 * out-of-scope. The parser trips `DMN.MISSING_DECISION_TABLE` when
 * `<decision>` carries something else as its body; a future ship can
 * widen the AST union to a sealed `DecisionBodyInterface` at that
 * seam without disturbing existing decision-table consumers.
 *
 * **Name vs id**: `$id` is the spec-required stable identifier the
 * deployer keys its VFS Node minting on (`/decisions/{id}/v{N}.dmn`).
 * `$name` is the human-friendly display string -- defaults to `$id`
 * when the XML omits it.
 */
final readonly class DecisionDefinitionAst
{
    public function __construct(
        public string $id,
        public string $name,
        public DecisionTableAst $decisionTable,
    ) {
    }
}

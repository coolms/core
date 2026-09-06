<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * Direction marker for gateway AST nodes per
 * `docs/investigations/m2c-design.md` §2.3 / §3.1.
 *
 * On `parallelGateway` (AND) the direction is author-declared and the
 * validator rejects mismatched `in`/`out` cardinality
 * (`WF.GATEWAY_DEGREE`). On `exclusiveGateway` (XOR) the direction is
 * derived by the parser from cardinality and not author-declared.
 */
enum GatewayDirection: string
{
    case Diverging = 'diverging';
    case Converging = 'converging';
}

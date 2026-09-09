<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Definition\DefinitionValidationException;
use CoolMS\Core\Definition\Severity;
use CoolMS\Core\Definition\Violation;
use CoolMS\Core\Definition\Violations;
use Throwable;

/**
 * Raised by `DmnXmlParser` in the consuming application when
 * the DMN 1.3 XML source cannot be turned into a
 * {@see \CoolMS\Core\Decision\DecisionDefinitionAst}.
 *
 * Mirrors `BpmnLiteParseException` in the consuming application
 * for the BPMN-Lite parser. Extends
 * {@see DefinitionValidationException} so the deployer catches
 * a single base type for parse + validate failures and renders one
 * Violations list to the editor.
 *
 * **DMN.* code catalogue** (parser-side; validator
 * codes layer on top):
 *  - `DMN.PARSE_ERROR`            -- malformed XML / libxml load failure.
 *  - `DMN.MISSING_DECISION`       -- no `<decision>` element in `<definitions>`.
 *  - `DMN.MISSING_DECISION_ID`    -- `<decision>` carries no `id` attribute.
 *  - `DMN.MISSING_DECISION_TABLE` -- `<decision>` body is not a decision table
 *                                    (literal expression, context, etc. -- out of scope at this stage).
 *  - `DMN.UNKNOWN_HIT_POLICY`     -- unknown hit-policy string on the table.
 *  - `DMN.UNKNOWN_AGGREGATOR`     -- unknown / wrongly-placed aggregator.
 *  - `DMN.EMPTY_DECISION_TABLE`   -- table has no `<rule>` rows.
 *  - `DMN.RULE_ARITY_MISMATCH`    -- rule's input/output entry count diverges
 *                                    from declared input/output column count.
 *  - `DMN.MISSING_RULE_ID`        -- `<rule>` carries no `id` attribute.
 *  - `DMN.MISSING_INPUT_EXPRESSION` -- `<input>` carries no `<inputExpression><text>`.
 *
 * `$sourceLocation` is accepted by the constructor but always passed
 * as `null` at this stage (mirrors the parser's deferred decision; line/column
 * tracking lands when the editor needs it for inline marking).
 */
final class DmnParseException extends DefinitionValidationException
{
    public readonly string $violationCode;
    public readonly string $path;
    public readonly ?SourceLocation $sourceLocation;

    public function __construct(
        string $violationCode,
        string $path,
        string $message,
        ?Throwable $previous = null,
        ?SourceLocation $sourceLocation = null,
    ) {
        $this->violationCode = $violationCode;
        $this->path = $path;
        $this->sourceLocation = $sourceLocation;

        $violations = new Violations();
        $violations->add(new Violation(
            code: $violationCode,
            path: $path,
            message: $message,
            severity: Severity::Error,
            context: [],
        ));

        parent::__construct($violations, $previous);
    }
}

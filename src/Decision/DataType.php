<?php

declare(strict_types=1);
namespace CoolMS\Core\Decision;

/**
 * Input/output column data types. Maps the DMN 1.3
 * `typeRef` attribute on `<input>` / `<output>` clauses. The
 * evaluator uses these to coerce EL-evaluated cell
 * expressions before unary-test matching.
 *
 * `Any` is the catch-all when the author leaves `typeRef` unset --
 * the evaluator passes values through without coercion. Bespoke
 * typeRefs outside this closed set (e.g. an XSD-namespaced custom
 * type) are coerced to `Any` per DMN spec lenience; a future ship
 * can add a contributor seam for tenant-defined typeRefs.
 *
 * String values match the DMN 1.3 builtin typeRef literals
 * (lowercase as the spec defines them) for direct attribute
 * comparison.
 */
enum DataType: string
{
    case String = 'string';
    case Number = 'number';
    case Boolean = 'boolean';
    case Date = 'date';
    case Time = 'time';
    case DateTime = 'dateTime';
    case Any = 'Any';

    /**
     * Resolve a `typeRef` attribute string with lenient fallback. The
     * DMN 1.3 spec allows unknown typeRefs (vendor extensions, XSD
     * imports); at this stage we collapse those to {@see Any}
     * rather than tripping a parse error, mirroring the BPMN-Lite
     * "lenient parser, strict validator" split.
     */
    public static function fromTypeRef(?string $typeRef): self
    {
        if (null === $typeRef || '' === $typeRef) {
            return self::Any;
        }

        return self::tryFrom($typeRef) ?? self::Any;
    }
}

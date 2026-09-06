<?php

declare(strict_types=1);

namespace CoolMS\Core\Api;

use Attribute;

/**
 * Describes one route so it appears in the platform's OpenAPI documentation.
 *
 * ## Two documents, and the relationship between them
 *
 * There is ONE document with a selection taken out of it, not two documents:
 *
 *   - the COMPLETE document is the platform's whole surface. It is the absence
 *     of a filter rather than a second filter, which is why a route cannot fall
 *     out of both: a resource is in it by construction.
 *   - the PUBLIC reference is the CONTRACT -- a selection from the complete
 *     document, made by `contract: true`.
 *
 * "Internal" is not an audience, and a document named by exclusion invites the
 * question of why it contains public endpoints. It contains them because it
 * contains everything.
 *
 * ## Why a plain controller needs this and a resource does not
 *
 * An API Platform resource is in the complete document whether or not anyone
 * marks it. A plain Symfony controller is not a resource, so the generator
 * cannot see it, and without this attribute it appears in NO document.
 *
 * ⚠️ So this attribute does two jobs on two kinds of route, and nothing at the
 * call site says which. On a plain controller it is what makes the route
 * documented at all; on a resource it only adds description. `contract` is a
 * separate question in both cases, asked separately.
 *
 * ## The opt-in asymmetry
 *
 * `contract` defaults to FALSE. Describing a route is safe and publishing it is
 * deliberate: forgetting `contract: true` costs a missing page in the public
 * reference, while a default of true would publish a promise nobody made.
 *
 * ## Why it lives in this package
 *
 * A published package cannot name an attribute that lives in the application
 * that consumes it -- so an attribute defined there can only describe the
 * application's own routes, and a package shipping routes of its own has no way
 * to describe them. That is a seam published at one end only, and this is the
 * end that ships.
 *
 * ⚠️ Deliberately NOT a swagger-php attribute. Those classes live in a
 * development dependency, so they do not exist in an installed application and
 * nothing can read them where it matters.
 *
 * ⚠️ The properties are `label`, `explains` and `sections` rather than the
 * obvious `summary`, `description` and `tags`. swagger-php walks EVERY
 * attribute on a method, not only its own, and merges any property whose name
 * matches one of its own -- an attribute carrying `summary` beside a
 * swagger-php operation attribute that also sets one reads as a competing
 * definition and takes the documentation UI down. Names that collide with
 * nothing cannot be merged into anything, which is why the schema properties
 * are `accepts` and `answers` rather than `requestBody` and `responses`.
 *
 * This is documentation metadata, not configurable wiring: it states one fixed
 * fact about the method it sits on, and nothing about it varies per install.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class ApiOperation
{
    /**
     * @param string             $label    one line, imperative, what the call does
     * @param bool               $contract true selects this route into the PUBLIC
     *                                     reference as well. Default false: the
     *                                     route is documented on the platform's
     *                                     complete surface and promises nothing to
     *                                     an outside caller.
     * @param string             $explains what the signature cannot say: what the
     *                                     operation means here, what it consumes and
     *                                     what a caller does with the result. A
     *                                     description that restates the method and
     *                                     path earns nothing and should be omitted.
     * @param list<string>       $sections grouping in the reference; defaults to the
     *                                     controller's own area when empty
     * @param string|null        $accepts  FQCN of the request body. Usually
     *                                     unnecessary: a `#[MapRequestPayload]`
     *                                     parameter already says what the endpoint
     *                                     takes, and that is read automatically.
     *                                     Give it only where the signature cannot.
     * @param array<int, string> $answers  status => FQCN of the response body, or
     *                                     a plain sentence where there is no body
     *                                     to describe. An empty list documents a
     *                                     bare 200, which is a promise that the
     *                                     call returns nothing useful -- say so
     *                                     deliberately rather than by omission.
     */
    public function __construct(
        public string $label,
        public bool $contract = false,
        public string $explains = '',
        public array $sections = [],
        public ?string $accepts = null,
        public array $answers = [],
    ) {
    }
}

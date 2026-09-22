<?php

declare(strict_types=1);

namespace CoolMS\Core\Form;

use Attribute;

/**
 * Declares the form definition an API resource class renders with, by id.
 *
 * Placed on the resource class itself; the Form module collects every marked
 * class at compile time into its {@see FormIdRegistryInterface}, so nothing
 * reflects on a request. A client reads the form id from the API manifest and
 * never constructs one itself.
 *
 * Example:
 *   #[FormId('section:site_section')]
 *   final class SiteSectionResource { ... }
 *
 * The attribute lives here rather than in the Form module because the modules
 * that mark their resources sit at every level of the platform, some below
 * Form: a mark is a declaration to the platform, and the platform owns it.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class FormId
{
    public function __construct(
        public string $id,
    ) {
    }
}

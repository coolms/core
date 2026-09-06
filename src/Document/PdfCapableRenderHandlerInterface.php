<?php

declare(strict_types=1);
namespace CoolMS\Core\Document;

/**
 * Marker for a format module's async render handler that can also produce a
 * PDF, and therefore needs to know at compile time whether the PDF module is
 * installed (#1781).
 *
 * A marker rather than a method, deliberately: the handlers share no callable
 * surface — each takes its own message type — and the only thing this declares
 * is "fill my `$pdfAvailable` argument", which
 * `DetectPdfModulePass` in the consuming application
 * does.
 *
 * ## Why an interface and not just the tag
 *
 * The tag alone does not survive. A module Extension's `addTag()` on an `App\`
 * class is silently dropped when the project's `App\` prototype loader
 * re-registers the same class — the definition that reaches the container is
 * the glob's, tagless. That is why the pass this replaces matched
 * `RenderWordInstanceHandler` by class NAME.
 *
 * Autoconfiguration is immune: it applies to whichever definition survives, so
 * `registerForAutoconfiguration()` in Document's Extension tags the handler no
 * matter who won. The explicit tags in the format Extensions stay as well, for
 * the reverse case where a module registers with autoconfiguration off.
 */
interface PdfCapableRenderHandlerInterface
{
}

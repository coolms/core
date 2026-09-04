<?php

declare(strict_types=1);

namespace CoolMS\Core\Seed;

/**
 * Seed a set of pages, refusing to overwrite anything a human has edited.
 *
 * The rule is {@see SeedGuard}'s; this walks a list and applies it, recording
 * what happened per page. It is shared by theme content seeding and module
 * installers -- the docs importer uses the guard directly because it is a
 * standalone HTTP tool with its own transport.
 *
 * ⚠️ Pure apart from the {@see SeedTargetInterface} it is handed, so the whole
 * of the seeding behaviour is unit-testable without a database.
 */
final readonly class ContentSeeder
{
    public function __construct(
        private SeedGuard $guard,
        private SeedTargetInterface $target,
    ) {
    }

    /**
     * @param iterable<SeedPage> $pages
     */
    public function seed(iterable $pages, bool $force = false): SeedRun
    {
        $run = new SeedRun();

        foreach ($pages as $page) {
            $extras = $this->target->readExtras($page->path);
            $live = $this->target->readBody($page->path, $page->locale);

            // ⚠️ KNOWN GAP: this decides on the BODY only, and a page's content
            // is not always its body -- a landing page keeps its sections in
            // `extras.blocks`. Two consequences, and the second is the serious
            // one:
            //
            //   - a manifest whose blocks changed reports `unchanged`, so the
            //     new sections never arrive;
            //   - if the body still matches the recorded hash the guard calls
            //     the page UNEDITED, so a later body change rewrites the extras
            //     and discards blocks an editor arranged in the admin.
            //
            // Fingerprinting body + extras was tried and is NOT sufficient: the
            // recorded hash covers the keys the PREVIOUS run wrote, while the
            // live side can only be built from the CURRENT run's key set, so a
            // run whose keys changed can never match and reports every page as
            // edited. `VfsSeedTargetTest` caught exactly that. The fix needs the
            // marker to record WHICH keys it wrote, which changes
            // `SeedGuard::marker()` and is a deliberate change rather than a
            // patch smuggled in here.
            $decision = $this->guard->decide($extras, $live, $page->body, $force);
            $run->record($page->path, $decision, $this->guard->reasonFor($decision, $extras, $live));

            if (!$decision->writes()) {
                continue;
            }

            // ⚠️ The marker is computed from the bytes being written and merged
            // into the EXISTING extras, so another seeder's marker and whatever
            // the editor set both survive. Written with the body rather than
            // after it, because this port has one write: there is no window in
            // which a marker could claim a state the body never reached.
            $this->target->write(
                $page->path,
                $page->locale,
                $page->body,
                $page->extras + [SeedGuard::EXTRAS_KEY => $this->guard->marker($page->body, $extras)],
            );
        }

        return $run;
    }
}

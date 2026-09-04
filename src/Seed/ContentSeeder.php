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

            // ⚠️ The EXTRAS are part of the comparison, not just the body. A
            // page's content is not always its body: a landing page keeps its
            // sections in `extras.blocks`, and a guard watching only the body
            // both missed manifest changes (reporting `unchanged` while new
            // sections never arrived) and, worse, called such a page unedited
            // -- so the next body change rewrote the extras and discarded blocks
            // an editor had arranged in the admin.
            //
            // The marker records which keys it wrote, so the two sides are
            // compared over the same key set. See `SeedGuard::isEdited()` for
            // why it has to be the RECORDED keys rather than this run's.
            $decision = $this->guard->decide($extras, $live, $page->body, $force, $page->extras);
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
                $page->extras + [SeedGuard::EXTRAS_KEY => $this->guard->marker($page->body, $extras, $page->extras)],
            );
        }

        return $run;
    }
}

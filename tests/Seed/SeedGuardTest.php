<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Seed;

use CoolMS\Core\Seed\SeedDecision;
use CoolMS\Core\Seed\SeedGuard;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function hash;

/**
 * The one rule three seeders share: never clobber somebody's edit.
 *
 * The cases that matter are the ones where a naive implementation is wrong --
 * an absent marker, another seeder's marker, and a renamed artefact.
 */
final class SeedGuardTest extends TestCase
{
    private const string ME = 'theme:coolms-default';

    #[Test]
    public function nothingThereMeansCreate(): void
    {
        self::assertSame(
            SeedDecision::Create,
            $this->guard()->decide(null, null, 'body'),
        );
    }

    #[Test]
    public function unchangedSinceThisSeederWroteItIsANoOp(): void
    {
        // Same bytes in, same bytes stored, and the marker proves we wrote them.
        self::assertSame(
            SeedDecision::SkipUnchanged,
            $this->guard()->decide($this->extrasFor('body'), 'body', 'body'),
        );
    }

    #[Test]
    public function untouchedButOutOfDateIsOverwritten(): void
    {
        // !! THE CASE A NAIVE IMPLEMENTATION GETS WRONG. The stored body differs
        // from the incoming one, which looks like a conflict -- but it matches
        // the recorded hash, so nobody has edited it and this is exactly the
        // update a seeder exists to make. Comparing incoming-to-stored instead
        // of stored-to-RECORDED would refuse every legitimate update.
        self::assertSame(
            SeedDecision::Overwrite,
            $this->guard()->decide($this->extrasFor('old'), 'old', 'new'),
        );
    }

    #[Test]
    public function anEditIsRefused(): void
    {
        // Stored body no longer matches what we recorded: a human has been here.
        self::assertSame(
            SeedDecision::RefuseEdited,
            $this->guard()->decide($this->extrasFor('mine'), 'theirs', 'mine'),
        );
    }

    #[Test]
    public function noMarkerIsRefusedRatherThanAssumedSafe(): void
    {
        // !! Seeded before the guard existed, or made by hand. There is no
        // baseline, so an edit cannot be ruled out. Refusing costs one --force;
        // assuming costs somebody their work.
        self::assertSame(
            SeedDecision::RefuseEdited,
            $this->guard()->decide(['title' => 'Hand made'], 'body', 'body'),
        );
    }

    #[Test]
    public function anotherSeedersMarkerDoesNotCountAsOurs(): void
    {
        // Two seeders may own artefacts in the same tree. Reading a marker that
        // is not ours would let one silently overwrite the other's work.
        $foreign = ['seed' => ['docs:concepts' => ['hash' => hash('sha256', 'body')]]];

        self::assertSame(
            SeedDecision::RefuseEdited,
            $this->guard()->decide($foreign, 'body', 'body'),
        );
    }

    #[Test]
    public function anOccupiedPathIsRefusedEvenThoughThisLocaleIsEmpty(): void
    {
        // !! THE CASE THE MARKER CANNOT COVER. Somebody wrote a page here in
        // `en`; the theme is seeding `uk`, so there is no live body to compare
        // and every marker check passes vacuously. Asking "is a body missing?"
        // says free path; asking "is the PATH free?" says occupied.
        self::assertSame(
            SeedDecision::RefuseOccupied,
            $this->guard()->decide(['title' => 'Their page'], null, 'seeded'),
        );
    }

    #[Test]
    public function ourOwnNodeInAnotherLocaleIsStillOurs(): void
    {
        // The other half of the occupancy rule. A second locale on a page this
        // seeder created must go in, or a multilingual theme would seed exactly
        // one language and refuse the rest of its own content.
        self::assertSame(
            SeedDecision::Create,
            $this->guard()->decide($this->extrasFor('body'), null, 'other locale'),
        );
    }

    #[Test]
    public function anOccupiedPathIsADifferentOutcomeFromAnEdit(): void
    {
        // Both leave the artefact alone and the operator's next move differs:
        // an edit is theirs to keep, an occupied path means the seeded page
        // exists NOWHERE and something has to move.
        $occupied = SeedDecision::RefuseOccupied;

        self::assertFalse($occupied->writes());
        self::assertTrue($occupied->needsReporting());
        self::assertNotSame(SeedDecision::RefuseEdited, $occupied);
    }

    #[Test]
    public function switchingThemesNamesTheThemeThatOwnsWhatIsInTheWay(): void
    {
        // !! A section carries several themes over its life, so "no seed marker
        // recorded" would be FALSE here -- there is one, it is somebody else's.
        // Reporting a missing baseline sends the operator looking for a hand
        // edit that never happened.
        $seededByAnother = ['seed' => ['theme:other' => ['hash' => hash('sha256', 'theirs')]]];
        $g = $this->guard();

        self::assertStringContainsString('theme:other', $g->reasonEdited($seededByAnother));
        self::assertStringContainsString('theme:other', $g->reasonOccupied($seededByAnother));
        self::assertStringNotContainsString('cannot prove', $g->reasonEdited($seededByAnother));
    }

    #[Test]
    public function theReasonForADecisionTellsTheTwoRefusalsApart(): void
    {
        // From the extras alone the two are identical -- an occupied path has no
        // live body, and neither does a page about to be created. The live body
        // is what separates them.
        $g = $this->guard();
        $handMade = ['title' => 'Theirs'];

        self::assertStringContainsString(
            'already exists at this path',
            $g->reasonFor(SeedDecision::RefuseOccupied, $handMade, null),
        );
        self::assertStringContainsString(
            'cannot prove',
            $g->reasonFor(SeedDecision::RefuseEdited, $handMade, 'their body'),
        );
        self::assertSame('', $g->reasonFor(SeedDecision::Create, null, null), 'a routine outcome needs no reason');
    }

    #[Test]
    public function forceReachesAnOccupiedPathToo(): void
    {
        // Otherwise the refusal is a dead end: no marker can ever appear on a
        // node the seeder is not allowed to touch, so nothing would ever unstick
        // it. `--force` is the operator's answer, and it is reported by name.
        self::assertSame(
            SeedDecision::ForcedOverwrite,
            $this->guard()->decide(['title' => 'Theirs'], null, 'seeded', force: true),
        );
    }

    #[Test]
    public function forceOverwritesButIsADistinctDecisionSoItCanBeReported(): void
    {
        // !! Not the same value as Overwrite. The caller has to be able to name
        // whose edits it is discarding, and a shared "we wrote it" outcome would
        // make that impossible to tell apart from a routine update.
        $decision = $this->guard()->decide($this->extrasFor('mine'), 'theirs', 'mine', force: true);

        self::assertSame(SeedDecision::ForcedOverwrite, $decision);
        self::assertTrue($decision->writes());
        self::assertTrue($decision->needsReporting());
    }

    #[Test]
    public function theLegacyFlatMarkerIsStillRead(): void
    {
        // The docs importer's `importedHash`. Without this, adopting the guard
        // would refuse every page it has ever written -- safe, correct, and
        // indistinguishable from the change being broken.
        self::assertSame(
            SeedDecision::SkipUnchanged,
            $this->guard()->decide(['importedHash' => hash('sha256', 'body')], 'body', 'body'),
        );
    }

    #[Test]
    public function theMarkerIsKeyedBySeederAndPreservesOthers(): void
    {
        $existing = ['seed' => ['docs:concepts' => ['hash' => 'theirs']], 'order' => 3];

        $marker = $this->guard()->marker('body', $existing);

        self::assertSame('theirs', $marker['docs:concepts']['hash'], 'another seeder\'s marker must survive');
        self::assertSame(hash('sha256', 'body'), $marker[self::ME]['hash']);
    }

    #[Test]
    public function identitySurvivesARename(): void
    {
        // The whole reason provenance is not keyed on the slug: rename the page
        // and the seeder must still know the node is its own, rather than
        // creating a duplicate that nobody connects to the rename.
        $extras = $this->extrasFor('body');

        // Nothing about the decision consults a slug, title or id -- the same
        // extras decide the same way whatever the artefact is now called.
        self::assertSame(
            SeedDecision::SkipUnchanged,
            $this->guard()->decide($extras, 'body', 'body'),
        );
    }

    #[Test]
    public function isEditedAnswersTheSameRuleWithoutTheIncomingBytes(): void
    {
        // The docs importer decides before it has rendered its Markdown, and
        // rendering costs an API call per page it is about to skip. Same rule,
        // one less input -- not a second implementation of it.
        $g = $this->guard();

        self::assertFalse($g->isEdited($this->extrasFor('body'), 'body'), 'ours and untouched');
        self::assertTrue($g->isEdited($this->extrasFor('mine'), 'theirs'), 'edited');
        self::assertTrue($g->isEdited(['title' => 'x'], 'body'), 'no marker means unprovable');
        self::assertFalse($g->isEdited(null, null), 'absent is not edited, it is new');
    }

    #[Test]
    public function theRefusalReasonDistinguishesAnEditFromAMissingBaseline(): void
    {
        // The operator's next move differs: restore somebody's work, or accept
        // that a hand-made page has no baseline and force it once.
        $g = $this->guard();

        self::assertStringContainsString('edited since', $g->reasonEdited($this->extrasFor('x')));
        self::assertStringContainsString('cannot prove', $g->reasonEdited(['title' => 'x']));
    }

    // ------------------------------------------------------------------
    // A page's content is not always its BODY.
    //
    // A landing page keeps its sections in `extras['blocks']`. A guard that
    // compared only bodies was wrong twice over, and the second one destroyed
    // work: it reported a changed manifest as `unchanged` so new sections never
    // arrived, and it called a rearranged page UNEDITED -- so the next body
    // change rewrote the extras and discarded the arrangement.
    // ------------------------------------------------------------------

    #[Test]
    public function rearrangingTheBlocksIsAnEditEvenWhenTheBodyIsUntouched(): void
    {
        // !! THE ONE THAT DESTROYED WORK. Body byte-identical to what was
        // seeded, so a body-only guard says "unedited" and overwrites -- taking
        // the editor's arrangement with it.
        $seeded = $this->seeded('body', ['blocks' => ['hero', 'pricing']]);
        $live = $this->live($seeded, ['blocks' => ['pricing', 'hero']]);

        self::assertTrue($this->guard()->isEdited($live, 'body'));
        self::assertSame(
            SeedDecision::RefuseEdited,
            $this->guard()->decide($live, 'body', 'body', false, ['blocks' => ['hero', 'pricing']]),
        );
    }

    #[Test]
    public function aManifestWhoseBlocksChangedIsNotUnchanged(): void
    {
        // The other half: nobody has edited anything, and the seeder has new
        // sections to deliver. A body-only guard reports SkipUnchanged and they
        // never arrive.
        $seeded = $this->seeded('body', ['blocks' => ['hero']]);

        self::assertSame(
            SeedDecision::Overwrite,
            $this->guard()->decide($seeded, 'body', 'body', false, ['blocks' => ['hero', 'faq']]),
        );
    }

    #[Test]
    public function sameBodyAndSameBlocksIsStillANoOp(): void
    {
        $seeded = $this->seeded('body', ['blocks' => ['hero'], 'title' => 'Home']);

        self::assertSame(
            SeedDecision::SkipUnchanged,
            $this->guard()->decide($seeded, 'body', 'body', false, ['blocks' => ['hero'], 'title' => 'Home']),
        );
    }

    #[Test]
    public function deletingASeededKeyIsAnEdit(): void
    {
        // Recorded and now absent. The subset differs, so the fingerprint does.
        $seeded = $this->seeded('body', ['blocks' => ['hero'], 'title' => 'Home']);
        $live = $seeded;
        unset($live['title']);

        self::assertTrue($this->guard()->isEdited($live, 'body'));
    }

    #[Test]
    public function aKeyTheSeederNeverWroteIsNotAnEdit(): void
    {
        // The editor set something of their own. It is not in the recorded key
        // set, so it is none of the seeder's business -- and treating it as an
        // edit would refuse every page the moment anyone touched the admin.
        $seeded = $this->seeded('body', ['blocks' => ['hero']]);
        $live = $seeded + ['editorNote' => 'mine'];

        self::assertFalse($this->guard()->isEdited($live, 'body'));
    }

    #[Test]
    public function aMarkerFromBeforeTheKeysWereRecordedFallsBackToTheBody(): void
    {
        // !! Refusing every page seeded by an earlier version would be safe,
        // correct, and would make this change look broken -- the same reasoning
        // that made the flat `importedHash` readable. The keys land on the next
        // write.
        $legacy = $this->extrasFor('body') + ['blocks' => ['whatever']];

        self::assertFalse($this->guard()->isEdited($legacy, 'body'));
    }

    #[Test]
    public function theMarkerRecordsItsOwnKeysAndNotItself(): void
    {
        $marker = $this->guard()->marker('body', null, ['blocks' => ['hero'], 'title' => 'Home']);

        self::assertSame(['blocks', 'title'], $marker[self::ME]['keys'], 'sorted, so key order cannot matter');
        self::assertNotContains(
            SeedGuard::EXTRAS_KEY,
            $marker[self::ME]['keys'],
            'the marker is provenance, not content -- covering itself makes the fingerprint self-referential',
        );
    }

    private function guard(): SeedGuard
    {
        return new SeedGuard(self::ME);
    }

    /** @return array<string, mixed> */
    private function extrasFor(string $body): array
    {
        return ['seed' => [self::ME => ['hash' => hash('sha256', $body)]]];
    }

    /**
     * The extras as they are stored AFTER a seed run -- the written keys plus
     * the marker, exactly as `ContentSeeder` assembles them.
     *
     * @param array<string, mixed> $written
     *
     * @return array<string, mixed>
     */
    private function seeded(string $body, array $written): array
    {
        return $written + [SeedGuard::EXTRAS_KEY => $this->guard()->marker($body, null, $written)];
    }

    /**
     * That artefact after somebody edited it in the admin: the marker is
     * untouched (nothing rewrites it), the values are not.
     *
     * @param array<string, mixed> $seeded
     * @param array<string, mixed> $changes
     *
     * @return array<string, mixed>
     */
    private function live(array $seeded, array $changes): array
    {
        return $changes + $seeded;
    }
}

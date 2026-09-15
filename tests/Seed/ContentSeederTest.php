<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Seed;

use CoolMS\Core\Seed\ContentSeeder;
use CoolMS\Core\Seed\SeedGuard;
use CoolMS\Core\Seed\SeedPage;
use CoolMS\Core\Seed\SeedTargetInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Seeding behaviour end to end, against an in-memory target.
 *
 * The cases worth having are the ones about somebody else's work: an edited
 * page, a page another seeder owns, and what `--force` is obliged to report.
 */
final class ContentSeederTest extends TestCase
{
    #[Test]
    public function anEmptyTargetIsPopulated(): void
    {
        $target = new InMemorySeedTarget();
        $run = $this->seeder($target)->seed([
            new SeedPage('/content/a', 'en', 'A', ['title' => 'A']),
            new SeedPage('/content/b', 'en', 'B'),
        ]);

        self::assertSame(['/content/a', '/content/b'], $run->created);
        self::assertSame('A', $target->readBody('/content/a', 'en'));
        self::assertSame('A', $target->readExtras('/content/a')['title'] ?? null);
    }

    #[Test]
    public function aSecondRunChangesNothing(): void
    {
        $target = new InMemorySeedTarget();
        $pages = [new SeedPage('/content/a', 'en', 'A')];

        $this->seeder($target)->seed($pages);
        $writesAfterFirst = $target->writes;
        $run = $this->seeder($target)->seed($pages);

        self::assertSame(['/content/a'], $run->unchanged);
        self::assertSame($writesAfterFirst, $target->writes, 'a no-op run must not write');
    }

    #[Test]
    public function anUpdatedSourceIsAppliedToAnUneditedPage(): void
    {
        $target = new InMemorySeedTarget();
        $this->seeder($target)->seed([new SeedPage('/content/a', 'en', 'old')]);

        $run = $this->seeder($target)->seed([new SeedPage('/content/a', 'en', 'new')]);

        self::assertSame(['/content/a'], $run->updated);
        self::assertSame('new', $target->readBody('/content/a', 'en'));
    }

    #[Test]
    public function anEditedPageIsRefusedAndNamedWithAReason(): void
    {
        $target = new InMemorySeedTarget();
        $this->seeder($target)->seed([new SeedPage('/content/a', 'en', 'seeded')]);

        // Somebody opens it in the admin and changes a word.
        $target->bodies['/content/a|en'] = 'seeded, edited by a human';

        $run = $this->seeder($target)->seed([new SeedPage('/content/a', 'en', 'seeded v2')]);

        self::assertSame([], $run->updated);
        self::assertArrayHasKey('/content/a', $run->refused);
        self::assertStringContainsString('edited', $run->refused['/content/a']);
        self::assertTrue($run->needsAttention());
        self::assertSame('seeded, edited by a human', $target->readBody('/content/a', 'en'), 'their work stands');
    }

    #[Test]
    public function forceOverwritesAndReportsWhatItDiscarded(): void
    {
        // !! The point of the separate outcome: an operator who forces must be
        // told WHICH pages lost edits, not a count. Silently destroying work
        // behind a flag is the failure this exists to prevent.
        $target = new InMemorySeedTarget();
        $this->seeder($target)->seed([new SeedPage('/content/a', 'en', 'seeded')]);
        $target->bodies['/content/a|en'] = 'human work';

        $run = $this->seeder($target)->seed([new SeedPage('/content/a', 'en', 'seeded v2')], force: true);

        self::assertArrayHasKey('/content/a', $run->overwritten);
        self::assertSame([], $run->refused);
        self::assertSame('seeded v2', $target->readBody('/content/a', 'en'));
        self::assertTrue($run->needsAttention());
    }

    #[Test]
    public function aThemeDoesNotWriteIntoAPageSomebodyElseMade(): void
    {
        // !! The worst outcome available here, and the one the marker cannot
        // stop: a hand-made front page, and a theme seeding a locale it does not
        // have. Nothing to compare, no marker to miss -- and without the
        // occupancy rule the theme merges its title, order and template over
        // somebody's page and adds a body to it.
        $target = new InMemorySeedTarget();
        $target->bodies['/content/default|en'] = 'A page a person wrote';
        $target->extras['/content/default'] = ['title' => 'Welcome'];

        $run = $this->seeder($target)->seed([new SeedPage('/content/default', 'uk', 'Themed', ['title' => 'Home'])]);

        self::assertSame([], $run->created);
        self::assertSame(0, $target->writes, 'nothing may be written into their node');
        self::assertArrayHasKey('/content/default', $run->occupied);
        self::assertSame('Welcome', $target->readExtras('/content/default')['title'] ?? null);
        self::assertTrue($run->needsAttention());
    }

    #[Test]
    public function anOccupiedPathIsReportedApartFromARefusedEdit(): void
    {
        // Counting them together would hide "this page exists nowhere" inside
        // the reassuring "we protected somebody's work".
        $target = new InMemorySeedTarget();
        $this->seeder($target)->seed([new SeedPage('/content/a', 'en', 'seeded')]);
        $target->bodies['/content/a|en'] = 'edited by hand';
        $target->extras['/content/b'] = ['title' => 'Not ours'];

        $run = $this->seeder($target)->seed([
            new SeedPage('/content/a', 'en', 'seeded v2'),
            new SeedPage('/content/b', 'en', 'B'),
        ]);

        self::assertSame(['/content/a'], array_keys($run->refused));
        self::assertSame(['/content/b'], array_keys($run->occupied));
        self::assertStringContainsString('1 refused, 1 occupied', $run->summary());
    }

    #[Test]
    public function aSecondLocaleOnItsOwnPageIsNotTreatedAsOccupied(): void
    {
        // The rule must not lock a seeder out of its own node, or a theme would
        // seed one language and refuse the rest of its own manifest.
        $target = new InMemorySeedTarget();
        $seeder = $this->seeder($target);
        $seeder->seed([new SeedPage('/content/a', 'en', 'English')]);

        $run = $seeder->seed([new SeedPage('/content/a', 'uk', 'Ukrainian')]);

        self::assertSame(['/content/a'], $run->created);
        self::assertSame('Ukrainian', $target->readBody('/content/a', 'uk'));
        self::assertSame('English', $target->readBody('/content/a', 'en'), 'the first locale stands');
    }

    #[Test]
    public function switchingThemesLeavesTheFirstThemesPagesAloneAndNamesIt(): void
    {
        // !! Theme A seeded, theme B is now bound. A's pages are the SITE's
        // content by then and an editor may have worked on them -- deleting
        // pages because somebody changed the design is not a trade any
        // installation would accept. So a section accumulates traces of every
        // theme it has carried, and B is told which theme is in its way.
        $target = new InMemorySeedTarget();
        new ContentSeeder(new SeedGuard('theme:a'), $target)
            ->seed([new SeedPage('/content/default', 'en', 'A home')]);

        $run = new ContentSeeder(new SeedGuard('theme:b'), $target)
            ->seed([new SeedPage('/content/default', 'en', 'B home')]);

        self::assertSame('A home', $target->readBody('/content/default', 'en'));
        self::assertStringContainsString('theme:a', $run->refused['/content/default'] ?? '');
    }

    #[Test]
    public function reActivatingTheSameThemeDoesNotMultiplyContent(): void
    {
        // Theme on, off, on again. Nothing removes content on the way out, so
        // the second binding meets its own pages -- and must recognise them.
        // Keyed on a slug or a title instead, a renamed page would come back as
        // a duplicate and nobody would connect it to the switching.
        $target = new InMemorySeedTarget();
        $pages = [new SeedPage('/content/default', 'en', 'Home')];

        $this->seeder($target)->seed($pages);
        $target->extras['/content/default']['title'] = 'Renamed by an editor';

        $run = $this->seeder($target)->seed($pages);

        self::assertSame(['/content/default'], $run->unchanged);
        self::assertSame(1, $target->writes, 'a re-activation must not write a second page');
        self::assertSame('Renamed by an editor', $target->readExtras('/content/default')['title'] ?? null);
    }

    #[Test]
    public function anotherSeedersMarkerIsPreservedNotClobbered(): void
    {
        // Two seeders may write into one tree. Replacing the extras bag rather
        // than merging would erase the other's provenance and make its next run
        // refuse its own page.
        $target = new InMemorySeedTarget();
        $target->extras['/content/a'] = ['seed' => ['docs:concepts' => ['hash' => 'theirs']], 'order' => 7];

        new ContentSeeder(new SeedGuard('theme:x'), $target)
            ->seed([new SeedPage('/content/a', 'en', 'A')], force: true);

        $extras = $target->readExtras('/content/a');
        self::assertIsArray($extras);
        self::assertArrayHasKey('seed', $extras);
        $seed = $extras['seed'];
        self::assertIsArray($seed);
        self::assertSame('theirs', $seed['docs:concepts']['hash']);
        self::assertArrayHasKey('theme:x', $seed);
    }

    private function seeder(SeedTargetInterface $target): ContentSeeder
    {
        return new ContentSeeder(new SeedGuard('theme:test'), $target);
    }
}

/**
 * An in-memory target. Deliberately dumb: it merges extras the way a real one
 * must, and counts writes so a "no-op" claim can be checked rather than assumed.
 */
final class InMemorySeedTarget implements SeedTargetInterface
{
    /** @var array<string, string> */
    public array $bodies = [];

    /** @var array<string, array<string, mixed>> */
    public array $extras = [];

    public int $writes = 0;

    public function readBody(string $path, string $locale): ?string
    {
        return $this->bodies[$path . '|' . $locale] ?? null;
    }

    public function readExtras(string $path): ?array
    {
        return $this->extras[$path] ?? null;
    }

    public function write(string $path, string $locale, string $body, array $extras): void
    {
        ++$this->writes;
        $this->bodies[$path . '|' . $locale] = $body;
        $this->extras[$path] = array_replace($this->extras[$path] ?? [], $extras);
    }
}

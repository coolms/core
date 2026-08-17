<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Page;

use CoolMS\Core\Page\PageSize;
use CoolMS\Core\Page\PageSizeResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * The single page-size catalog shared by every render surface: web max-width,
 * DOCX paper dimensions, and the admin option list all resolve from one place,
 * and an unset / unknown size degrades to "no change" everywhere.
 */
#[CoversClass(PageSizeResolver::class)]
#[CoversClass(PageSize::class)]
final class PageSizeResolverTest extends TestCase
{
    private PageSizeResolver $resolver;

    public function testPaperSizesYieldRealWebWidths(): void
    {
        self::assertSame('210mm', $this->resolver->webMaxWidth(['pageSize' => 'a4']));
        self::assertSame('8.5in', $this->resolver->webMaxWidth(['pageSize' => 'letter']));
        self::assertSame('8.5in', $this->resolver->webMaxWidth(['pageSize' => 'legal']));
        self::assertSame('1320px', $this->resolver->webMaxWidth(['pageSize' => 'wide']));
        self::assertSame('100%', $this->resolver->webMaxWidth(['pageSize' => 'full']));
    }

    public function testCustomWebWidthUsesThePixelValue(): void
    {
        self::assertSame('960px', $this->resolver->webMaxWidth(['pageSize' => 'custom', 'pageWidth' => 960]));
        // Numeric string accepted (extras round-trip through JSON / merge-patch).
        self::assertSame('640px', $this->resolver->webMaxWidth(['pageSize' => 'custom', 'pageWidth' => '640']));
    }

    public function testCustomWithoutOrWithBadWidthFallsBackToNoOverride(): void
    {
        self::assertNull($this->resolver->webMaxWidth(['pageSize' => 'custom']));
        self::assertNull($this->resolver->webMaxWidth(['pageSize' => 'custom', 'pageWidth' => 0]));
        self::assertNull($this->resolver->webMaxWidth(['pageSize' => 'custom', 'pageWidth' => -5]));
    }

    public function testUnsetOrUnknownSizeIsNeverAnOverride(): void
    {
        self::assertNull($this->resolver->webMaxWidth([]));
        self::assertNull($this->resolver->webMaxWidth(['pageSize' => '']));
        self::assertNull($this->resolver->webMaxWidth(['pageSize' => 'tabloid']));
        self::assertNull($this->resolver->docxSection([]));
        self::assertNull($this->resolver->docxSection(['pageSize' => 'nonsense']));
    }

    public function testDocxDimensionsForPaperSizes(): void
    {
        // A4 = 210x297mm at 56.6929 twips/mm.
        self::assertSame(
            ['pageSizeW' => 11906, 'pageSizeH' => 16838, 'orientation' => 'portrait'],
            $this->resolver->docxSection(['pageSize' => 'a4']),
        );
        // Letter = 8.5x11in.
        self::assertSame(
            ['pageSizeW' => 12240, 'pageSizeH' => 15840, 'orientation' => 'portrait'],
            $this->resolver->docxSection(['pageSize' => 'letter']),
        );
        // Legal = 8.5x14in.
        self::assertSame(
            ['pageSizeW' => 12240, 'pageSizeH' => 20160, 'orientation' => 'portrait'],
            $this->resolver->docxSection(['pageSize' => 'legal']),
        );
        // Wide = A4 landscape (dimensions swapped).
        self::assertSame(
            ['pageSizeW' => 16838, 'pageSizeH' => 11906, 'orientation' => 'landscape'],
            $this->resolver->docxSection(['pageSize' => 'wide']),
        );
        // A3 = 297x420mm.
        self::assertSame(
            ['pageSizeW' => 16838, 'pageSizeH' => 23811, 'orientation' => 'portrait'],
            $this->resolver->docxSection(['pageSize' => 'a3']),
        );
    }

    /**
     * Orientation is its own axis — `wide` was previously the only way
     * to get landscape, and it means A4 specifically, so A3 landscape or
     * Letter landscape could not be expressed at all.
     */
    public function testOrientationAppliesOnTopOfAnySize(): void
    {
        // A3 landscape — the combination that had no preset.
        self::assertSame(
            ['pageSizeW' => 23811, 'pageSizeH' => 16838, 'orientation' => 'landscape'],
            $this->resolver->docxSection(['pageSize' => 'a3', 'pageOrientation' => 'landscape']),
        );
        // Letter landscape.
        self::assertSame(
            ['pageSizeW' => 15840, 'pageSizeH' => 12240, 'orientation' => 'landscape'],
            $this->resolver->docxSection(['pageSize' => 'letter', 'pageOrientation' => 'landscape']),
        );
    }

    /**
     * BOTH halves must move together. PHPWord takes the marker AND the
     * dimensions; setting only the marker yields a document labelled landscape
     * on portrait paper, which opens looking wrong in Word while every value
     * we wrote reads back correct.
     */
    public function testOrientationSwapsDimensionsNotJustTheMarker(): void
    {
        $portrait = $this->resolver->docxSection(['pageSize' => 'a4']);
        $landscape = $this->resolver->docxSection(['pageSize' => 'a4', 'pageOrientation' => 'landscape']);

        self::assertNotNull($portrait);
        self::assertNotNull($landscape);
        self::assertSame($portrait['pageSizeW'], $landscape['pageSizeH']);
        self::assertSame($portrait['pageSizeH'], $landscape['pageSizeW']);
    }

    /**
     * Asking for the orientation a size already has must be a no-op, or
     * `wide` (which declares itself landscape) would swap BACK to portrait.
     */
    public function testRedundantOrientationDoesNotSwap(): void
    {
        self::assertSame(
            $this->resolver->docxSection(['pageSize' => 'wide']),
            $this->resolver->docxSection(['pageSize' => 'wide', 'pageOrientation' => 'landscape']),
        );
        self::assertSame(
            $this->resolver->docxSection(['pageSize' => 'a4']),
            $this->resolver->docxSection(['pageSize' => 'a4', 'pageOrientation' => 'portrait']),
        );
    }

    /** An unrecognised orientation is ignored, never a broken section. */
    public function testUnknownOrientationLeavesTheSizeAlone(): void
    {
        self::assertSame(
            $this->resolver->docxSection(['pageSize' => 'a4']),
            $this->resolver->docxSection(['pageSize' => 'a4', 'pageOrientation' => 'sideways']),
        );
        self::assertNull($this->resolver->orientation(['pageOrientation' => 'sideways']));
    }

    /**
     * The sheet an author sees is the paper the renderer sets — same numbers,
     * different unit. Asserting the millimetres directly (rather than "it is
     * non-null") is the point: a canvas drawn 210mm wide against a document
     * PHPWord lays out on Letter looks correct right up until it prints.
     */
    public function testSheetGeometryMatchesThePaperInMillimetres(): void
    {
        self::assertSame(
            ['width' => '210mm', 'height' => '297mm'],
            $this->resolver->sheetGeometry(['pageSize' => 'a4']),
        );
        self::assertSame(
            ['width' => '297mm', 'height' => '420mm'],
            $this->resolver->sheetGeometry(['pageSize' => 'a3']),
        );
        // Inches round-trip through twips too: 8.5in x 11in.
        self::assertSame(
            ['width' => '215.9mm', 'height' => '279.4mm'],
            $this->resolver->sheetGeometry(['pageSize' => 'letter']),
        );
    }

    /**
     * Orientation reaches the canvas because the geometry is DERIVED from
     * `docxSection()`, not from a second table of millimetres. If it were
     * copied, this is the test that would start failing the first time someone
     * added a size to one list and not the other.
     */
    public function testSheetGeometryFollowsOrientation(): void
    {
        self::assertSame(
            ['width' => '297mm', 'height' => '210mm'],
            $this->resolver->sheetGeometry(['pageSize' => 'a4', 'pageOrientation' => 'landscape']),
        );

        // And it is genuinely the same source: every catalog size's sheet must
        // agree with its own docxSection, whatever the orientation.
        foreach (['a4', 'a3', 'letter', 'legal', 'wide'] as $size) {
            foreach ([null, 'portrait', 'landscape'] as $orientation) {
                $extras = ['pageSize' => $size];
                if (null !== $orientation) {
                    $extras['pageOrientation'] = $orientation;
                }

                $section = $this->resolver->docxSection($extras);
                $sheet = $this->resolver->sheetGeometry($extras);

                self::assertNotNull($section);
                self::assertNotNull($sheet);
                // Wider paper must yield a wider sheet — a swap applied to one
                // and not the other shows up here as a transposed pair.
                self::assertSame(
                    $section['pageSizeW'] > $section['pageSizeH'],
                    (float) rtrim($sheet['width'], 'm') > (float) rtrim($sheet['height'], 'm'),
                    sprintf('%s/%s', $size, $orientation ?? 'unset'),
                );
            }
        }
    }

    /**
     * No paper, no sheet. `full` and `custom` are web widths that leave the
     * DOCX page at PHPWord's own default, so drawing a specific sheet for them
     * would be a claim about paper we never made.
     */
    public function testWebOnlyAndUnsetSizesHaveNoSheet(): void
    {
        self::assertNull($this->resolver->sheetGeometry(['pageSize' => 'full']));
        self::assertNull($this->resolver->sheetGeometry(['pageSize' => 'custom', 'pageWidth' => 800]));
        self::assertNull($this->resolver->sheetGeometry([]));
        self::assertNull($this->resolver->sheetGeometry(['pageSize' => 'nonsense']));
    }

    public function testWebOnlySizesLeaveTheDocxPageUnchanged(): void
    {
        self::assertNull($this->resolver->docxSection(['pageSize' => 'full']));
        self::assertNull($this->resolver->docxSection(['pageSize' => 'custom', 'pageWidth' => 800]));
    }

    public function testOptionsListMirrorsTheCatalogInOrder(): void
    {
        self::assertSame(
            [
                ['value' => 'a4', 'label' => 'A4'],
                ['value' => 'a3', 'label' => 'A3'],
                ['value' => 'letter', 'label' => 'Letter'],
                ['value' => 'legal', 'label' => 'Legal'],
                ['value' => 'wide', 'label' => 'Wide'],
                ['value' => 'full', 'label' => 'Full width'],
                ['value' => 'custom', 'label' => 'Custom'],
            ],
            $this->resolver->options(),
        );
    }

    public function testDocxOptionsKeepOnlyPresetsWithARealPaperMapping(): void
    {
        // The document-builder offers only sizes that change the printed page
        // (a non-null docxSection): A4 / A3 / Letter / Legal / Wide. The
        // web-only `full` / `custom` are excluded since they leave the DOCX
        // page at PHPWord's default and would be no-op choices there.
        self::assertSame(
            [
                ['value' => 'a4', 'label' => 'A4'],
                ['value' => 'a3', 'label' => 'A3'],
                ['value' => 'letter', 'label' => 'Letter'],
                ['value' => 'legal', 'label' => 'Legal'],
                ['value' => 'wide', 'label' => 'Wide'],
            ],
            $this->resolver->docxOptions(),
        );
    }

    public function testDocxOptionsStayConsistentWithDocxSection(): void
    {
        // Derived from the catalog, never hardcoded: every offered preset must
        // resolve to a real DOCX section, and none of the excluded ones may.
        foreach ($this->resolver->docxOptions() as $option) {
            self::assertNotNull(
                $this->resolver->docxSection(['pageSize' => $option['value']]),
                sprintf('Offered docx preset "%s" must map to a real page.', $option['value']),
            );
        }
        self::assertNull($this->resolver->docxSection(['pageSize' => 'full']));
        self::assertNull($this->resolver->docxSection(['pageSize' => 'custom']));
    }

    protected function setUp(): void
    {
        $this->resolver = new PageSizeResolver();
    }
}

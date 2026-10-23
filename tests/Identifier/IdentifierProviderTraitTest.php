<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Identifier;

use CoolMS\Core\Mapping\Column;
use CoolMS\Core\Mapping\GeneratedValue;
use CoolMS\Core\Mapping\Id;
use CoolMS\Core\Tests\Identifier\Fixture\IdentifiedRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Uid\Uuid;

/**
 * `IdentifierProviderTrait` gives a record its UUID identifier: a promoted
 * constructor property declared, through the persistence-neutral mapping
 * attributes, as a unique UUID column generated as UUIDv7, and a string form of
 * it for serialisation.
 *
 * The fixture is the consumer's shape: a record with its own constructor that
 * calls the trait's under an alias.
 */
final class IdentifierProviderTraitTest extends TestCase
{
    private const string ID = '0199b3a4-6f1e-7c2a-9d3e-0a1b2c3d4e5f';

    #[Test]
    public function theIdGivenIsTheIdKept(): void
    {
        $id = Uuid::fromString(self::ID);

        $record = new IdentifiedRecord(id: $id);

        self::assertSame($id, $record->id);
    }

    #[Test]
    public function theStringFormIsTheCanonicalUuid(): void
    {
        $record = new IdentifiedRecord(id: Uuid::fromString(self::ID));

        self::assertSame(self::ID, $record->idAsString);
    }

    #[Test]
    public function theIdIsMappedAsAUniqueUuidColumnGeneratedAsUuidV7(): void
    {
        $property = new ReflectionProperty(IdentifiedRecord::class, 'id');

        self::assertCount(1, $property->getAttributes(Id::class));

        $columns = $property->getAttributes(Column::class);
        self::assertCount(1, $columns);
        $column = $columns[0]->newInstance();
        self::assertSame('uuid', $column->type);
        self::assertTrue($column->unique);
        self::assertFalse($column->nullable);

        $generated = $property->getAttributes(GeneratedValue::class);
        self::assertCount(1, $generated);
        $generation = $generated[0]->newInstance();
        self::assertSame(GeneratedValue::CUSTOM, $generation->strategy);
        self::assertSame(GeneratedValue::UUID_V7, $generation->generator);
    }

    /**
     * A persistence layer builds a record without calling its constructor and
     * sets the columns afterwards. Until it has, the string form answers null
     * rather than failing on an uninitialised property.
     */
    #[Test]
    public function aRecordBuiltWithoutItsConstructorHasNoStringFormYet(): void
    {
        $record = new ReflectionClass(IdentifiedRecord::class)->newInstanceWithoutConstructor();

        self::assertNull($record->idAsString);
    }
}

<?php

declare(strict_types=1);

namespace CoolMS\Core\Block;

use InvalidArgumentException;

use function sprintf;

/**
 * Declares that a block's group field holds TEXT OVERRIDES for a form: a label or a help text per field of the form,
 * per locale. Which form is named by another field of the same block -- a choice -- through `$forms`.
 *
 * !! The overrides reach a form's words, never its shape. The module that stores the block checks every item against
 * the chosen form's definition when the block is saved (a field the form does not have, a locale the page has no
 * variant for, markup or an over-long text: refused), and the form renderer applies only a label and a help text to a
 * field that exists. The action, the field names, a field's type and the CSRF token are not reachable from here.
 */
final readonly class FormTexts
{
    /** The item keys a form-texts group holds, and nothing else. */
    public const string FIELD = 'field';
    public const string LOCALE = 'locale';
    public const string LABEL = 'label';
    public const string HELP = 'help';

    /** The longest label and help text an override may carry, in characters. */
    public const int LABEL_MAX = 80;
    public const int HELP_MAX = 300;

    /**
     * @param string                $choiceField the block field whose value picks the form
     * @param array<string, string> $forms       that field's value => the form's id
     */
    public function __construct(
        public string $choiceField,
        public array $forms,
    ) {
        if ('' === $choiceField || [] === $forms) {
            throw new InvalidArgumentException(sprintf('Form texts need a choice field and at least one form (got "%s").', $choiceField));
        }
    }

    /** The form a block names, or null when its choice names none. */
    public function formFor(mixed $choice): ?string
    {
        return is_string($choice) ? ($this->forms[$choice] ?? null) : null;
    }

    /**
     * The group's item fields, for a block type to declare: an ordinary group, so an admin that knows nothing of form
     * texts edits it as one.
     *
     * @return list<BlockField>
     */
    public static function itemFields(): array
    {
        return [
            new BlockField(self::FIELD),
            new BlockField(self::LOCALE),
            new BlockField(self::LABEL),
            new BlockField(self::HELP, BlockField::KIND_TEXTAREA),
        ];
    }
}

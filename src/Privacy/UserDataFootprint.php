<?php

declare(strict_types=1);

namespace CoolMS\Core\Privacy;

use Attribute;
use InvalidArgumentException;

use function implode;
use function in_array;
use function sprintf;

/**
 * A module's declaration of what it holds about a person and what it does
 * with it when the account is deleted, placed on the class that does it -- a
 * deletion handler, a retention pruner, or a class that exists only to say
 * "kept" -- beside {@see UserDataFootprintInterface}, which is what gets the
 * class collected.
 *
 * `action`: {@see ACTION_DELETE}, {@see ACTION_MINIMISE} (the record stays,
 * what identifies the person goes) or {@see ACTION_KEEP}. `canHold` says
 * whether the category is ABLE to keep the data under an obligation; whether
 * it does, and for how long, is the operator's configuration, never the
 * platform's default.
 *
 * The platform's privacy guarantee rests on these declarations: every module
 * declares its footprints, and account deletion reads them all before it
 * runs. That is why the declaration is the platform's and not any module's --
 * a module at any level declares by importing this, and the reader is the
 * module that deletes accounts.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class UserDataFootprint
{
    public const string ACTION_DELETE = 'delete';

    public const string ACTION_MINIMISE = 'minimise';

    public const string ACTION_KEEP = 'keep';

    private const array ACTIONS = [self::ACTION_DELETE, self::ACTION_MINIMISE, self::ACTION_KEEP];

    /**
     * @param string       $category dotted, module first: `consent.records`
     * @param string       $label    one sentence a person can read in the deletion report
     * @param list<string> $tables   the tables the category lives in
     * @param string       $action   one of the ACTION_* constants
     * @param bool         $canHold  whether an operator may keep the data under a stated obligation
     */
    public function __construct(
        public string $category,
        public string $label,
        public array $tables,
        public string $action,
        public bool $canHold = false,
    ) {
        if (!in_array($action, self::ACTIONS, true)) {
            $message = sprintf('A footprint action is one of %s, "%s" given.', implode(', ', self::ACTIONS), $action);

            throw new InvalidArgumentException($message);
        }
        if ('' === $category || '' === $label || [] === $tables) {
            throw new InvalidArgumentException('A footprint names its category, its label and at least one table.');
        }
    }
}

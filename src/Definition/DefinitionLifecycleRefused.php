<?php

declare(strict_types=1);
namespace CoolMS\Core\Definition;

use RuntimeException;

use function sprintf;

/**
 * A lifecycle operation was REFUSED by a guard -- the definition exists,
 * but the requested change conflicts with its current state.
 *
 * Carries a machine-readable {@see $reason} plus a human sentence that
 * says what to do INSTEAD. Modelled on `DeleteTagProcessor`, which
 * refuses with the reference count and the remedy rather than a bare
 * "cannot delete" -- an author who is told "it has 3 running instances,
 * retire it instead" can act; one who is told "conflict" cannot.
 *
 * Maps to HTTP 409 at the controller: the resource exists, the
 * OPERATION conflicts with its state.
 */
final class DefinitionLifecycleRefused extends RuntimeException
{
    public const string REASON_HAS_DEPLOYED_HISTORY = 'has_deployed_history';

    public const string REASON_HAS_RUNNING_INSTANCES = 'has_running_instances';

    public const string REASON_MODULE_SHIPPED = 'module_shipped';

    public function __construct(
        public readonly string $definitionKey,
        public readonly string $reason,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * Deployed versions are immutable audit history, so the definition
     * is retired rather than removed.
     */
    public static function hasDeployedHistory(string $key, int $version): self
    {
        return new self(
            $key,
            self::REASON_HAS_DEPLOYED_HISTORY,
            sprintf(
                'Definition "%s" has deployed history (latest v%d) and cannot be deleted; deployed bodies are immutable audit records. Retire it instead — that hides it from the active catalog and blocks new starts while preserving history.',
                $key,
                $version,
            ),
        );
    }

    /** Live tokens resolve their AST through the definition. */
    public static function hasRunningInstances(string $key, int $count): self
    {
        return new self(
            $key,
            self::REASON_HAS_RUNNING_INSTANCES,
            sprintf(
                'Definition "%s" has %d running instance(s) and cannot be deleted; they would be stranded without the definition to resolve. Let them finish (or cancel them), then retire it.',
                $key,
                $count,
            ),
        );
    }

    /**
     * A module-shipped definition is re-created by the installer on the
     * next cache warm, so removing or hiding it does not stick.
     */
    public static function moduleShipped(string $key, string $operation): self
    {
        return new self(
            $key,
            self::REASON_MODULE_SHIPPED,
            sprintf(
                'Definition "%s" is shipped by a module, so %s would not stick — the installer re-creates it on the next deploy. Uninstall or disable the owning module instead.',
                $key,
                $operation,
            ),
        );
    }
}

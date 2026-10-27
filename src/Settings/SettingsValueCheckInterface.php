<?php

declare(strict_types=1);

namespace CoolMS\Core\Settings;

/**
 * A module's check on the values of a settings block, beyond what the block's form can say.
 *
 * A form can say a field is required or numeric. It cannot say "phone sign-in needs a channel that can deliver a
 * confirmation code", because the answer depends on what else is installed and configured. The module that owns the
 * rule states it here, and the settings tier asks it.
 *
 * !! **One check, asked at two moments.** On a save, the settings tier asks with the values the block WOULD be
 * running on, and refuses the save on any refusal, naming each one to the admin. On a read of what is already stored,
 * a health check asks the same question and reports each refusal as a warning: a value saved before the check
 * existed, or one that became wrong when something it depends on was removed, must be visible and must not be
 * repaired in silence. Neither moment may make the setting's CONSUMERS fail: what a consumer does with a value the
 * check refuses is the consumer's own rule.
 *
 * The `coolms.settings.value_check` tag is applied by an `_instanceof` rule in the application, not by an attribute
 * here: a Domain contract must not import the container it happens to run in.
 *
 * !! Belongs to the KERNEL for the same layering reason as {@see ModuleSettingsDefinition}: a module low in the
 * graph may not import the settings implementation, and it is often exactly the module whose values need a rule.
 */
interface SettingsValueCheckInterface
{
    /**
     * The settings keys whose values this check reads, e.g. `['identity.identifiers']`.
     *
     * @return list<string>
     */
    public function checks(): array;

    /**
     * The refusals for one block's values, or none.
     *
     * `$values` is the whole block as it would be in force -- defaults, the platform row, the site's row when
     * `$scope` names one, and the write being made -- not only the keys a save changed, because a rule usually
     * relates one key to another.
     *
     * A refusable value is answered, never thrown: a throw is a broken check, and the settings tier reports it as
     * one rather than as a refusal.
     *
     * @param array<string, mixed> $values
     *
     * @return list<SettingsValueRefusal>
     */
    public function check(string $key, array $values, ?string $scope = null): array;
}

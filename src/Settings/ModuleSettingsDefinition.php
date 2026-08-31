<?php

declare(strict_types=1);

namespace CoolMS\Core\Settings;

/**
 * One admin-editable settings block, declared by the module that owns it.
 *
 * The declaration is what makes the settings endpoint safe. Without it the API
 * would be "write any config row", and the config store backs dashboards,
 * datagrids and navigraph trees as well as settings -- an admin settings screen
 * must not be a way to rewrite a navigation tree.
 *
 * ⚠️ Belongs to the KERNEL rather than to whichever module implements the
 * settings surface, and the reason is a layering one. An application that
 * enforces module boundaries typically bars a module from importing a SIBLING
 * module's domain types, and the settings implementation sits low -- so while
 * it owned this type, every module beside it could READ a setting, because an
 * interface is permitted across that line, but could not DECLARE one, because
 * a definition is a concrete type. That leaves an application whose upper
 * layers are configurable and whose foundation is not. The kernel is the one
 * dependency every module may take, so the contract lives here.
 */
final class ModuleSettingsDefinition
{
    /**
     * The values in force when nothing has been saved.
     *
     * A virtual property so every existing `$definition->defaults` call site is
     * unchanged whether the owning module declared them eagerly or lazily.
     *
     * @var array<string, mixed>
     */
    public array $defaults {
        get => $this->resolvedDefaults ??= ([] === $this->declaredDefaults && null !== $this->lazyDefaults
            ? ($this->lazyDefaults)()
            : $this->declaredDefaults);
    }

    /** @var array<string, mixed> */
    private readonly array $declaredDefaults;

    /** @var (callable(): array<string, mixed>)|null */
    private $lazyDefaults;

    /**
     * Memo for the lazy form. The class is therefore not `readonly` -- the only
     * mutable state it has, and it exists so a deferred default is computed at
     * most once per instance rather than on every property read.
     *
     * @var array<string, mixed>|null
     */
    private ?array $resolvedDefaults = null;

    /**
     * @param string                                  $key          the config-store id, e.g. `dynamic_chat.prechat`.
     *                                                              Must satisfy the store's KEY_PATTERN (letters,
     *                                                              digits, dot, underscore, dash) because it becomes
     *                                                              a path segment in the file half of the store.
     * @param string                                  $module       the owning module, for grouping in the UI
     * @param string                                  $label        human label for the settings page
     * @param string|null                             $formId       a Form definition id describing the fields, so the
     *                                                              admin page renders from the existing dynamic-form
     *                                                              vocabulary rather than a bespoke screen per module
     * @param string|null                             $moduleLabel  how the owning module is NAMED in the UI, e.g.
     *                                                              `Dynamic Chat`. The raw `$module` is a slug --
     *                                                              it groups correctly and reads badly as a
     *                                                              heading. Null falls back to the slug,
     *                                                              de-slugified.
     * @param string|null                             $moduleRoute  where the module's own admin page lives, as an
     *                                                              admin-relative route (`dynamic-chat`). Makes the
     *                                                              group heading a link back to the thing being
     *                                                              configured. Null = a module with no page of its
     *                                                              own, and the heading stays plain text.
     * @param string|null                             $moduleIcon   Bootstrap Icons name WITHOUT the `bi-` prefix,
     *                                                              for the group heading. Declared by the module
     *                                                              rather than looked up from its sidebar node:
     *                                                              one owner, no cross-module read, and no second
     *                                                              request to render a heading.
     * @param array<string, mixed>                    $defaults     see {@see $defaults}. ⚠️ Use `$lazyDefaults`
     *                                                              instead whenever computing these costs
     *                                                              anything: reading ONE block forces every
     *                                                              contributor to declare itself, so an eager
     *                                                              default is paid for by every reader of
     *                                                              every other block
     * @param array<string, string>                   $envPins      see {@see $envPins}
     * @param list<string>                            $nullableKeys see {@see $nullableKeys}
     * @param bool                                    $siteScopable see {@see $siteScopable}
     * @param (callable(): array<string, mixed>)|null $lazyDefaults deferred until this block is actually read,
     *                                                              and called at most once. Ignored when
     *                                                              `$defaults` is non-empty, so a contributor
     *                                                              cannot half-declare both
     */
    public function __construct(
        public string $key,
        public string $module,
        public string $label,
        public ?string $formId = null,
        public ?string $moduleLabel = null,
        public ?string $moduleIcon = null,
        public ?string $moduleRoute = null,
        /*
         * The values in force when nothing has been saved.
         *
         * A settings block used to expose only what an admin had STORED, so a
         * screen for a module running happily on its shipped defaults rendered
         * blank -- a required select reading "-- Select --" for a value the
         * system definitely has. The module knows those defaults (a container
         * parameter, an installer's group, a constant); nothing else can, so
         * the contributor states them here and the form renders
         * `stored ?? default`.
         *
         * Resolved per request, because a contributor is a service: a default
         * that depends on a seeded row or a parameter stays current.
         *
         * ⚠️ Pass `$lazyDefaults` instead whenever computing these costs
         * anything -- see the note on that parameter. Reading ONE block forces
         * every contributor to declare itself, so an eager default is paid for by
         * every reader of every other block.
         *
         * @var array<string, mixed>
         */
        array $defaults = [],
        /**
         * Keys this deployment may have PINNED, as `settings key => env var name`.
         *
         * A module states the mapping; whether a pin is actually in force depends
         * on the running environment, which is why {@see lockedKeys()} takes the
         * environment rather than this being a list of booleans.
         *
         * Declaring a pin here is what lets the admin say "set by PAGE_CACHE_TTL"
         * instead of offering a field that saves and does nothing. 20 of the
         * platform's 68 module parameters are `env()`-backed, so this is the
         * common case, not an exotic one.
         *
         * @var array<string, string>
         */
        public array $envPins = [],
        /**
         * Keys an admin may CLEAR, as a list of settings keys.
         *
         * ⚠️ **Stated, not inferred, and the inference was tried first.** An
         * earlier rule derived nullability from the declared default's type --
         * clear a key whose default is null, refuse one whose default is a
         * string. It made the same save valid on one install and refused on
         * another, because `dynamic_chat.prechat`'s `default_country` ships a
         * string where one is configured and null where it is not. Whether a key
         * may be cleared is a property of the FIELD; only the module knows it.
         *
         * A key absent from this list cannot be saved as `null`. That is the
         * strict default on purpose: a required control saved as null reads back
         * on screen as "cleared" while its consumer falls back to the shipped
         * value, so the screen describes a configuration that is not running.
         *
         * The form is the other half of this fact, and a sweep holds them
         * together in the direction that matters: a field the form marks
         * REQUIRED must never appear here. The reverse is deliberately NOT
         * enforced -- "not required" and "nullable" are close but not the same
         * (a toggle is never null, and is not required either), so forcing them
         * equal would make modules declare things they do not mean.
         *
         * @var list<string>
         */
        public array $nullableKeys = [],
        /**
         * Whether one site may override this block's values.
         *
         * ⚠️ **Off by default, and a scoped write to a block that has not opted
         * in is REFUSED.** Not ignored: a per-site row nothing reads is the same
         * silent failure as an undeclared key -- it saves, reads back, and
         * changes nothing.
         *
         * Most blocks are genuinely platform-wide. Agent access decides who may
         * read every live conversation across the whole install; asking it "for
         * which site?" has no answer. A page cache TTL does: a news site and a
         * documentation site want different numbers, and that is the difference
         * this flag records.
         */
        public bool $siteScopable = false,
        /*
         * Defaults that cost something to work out, deferred until this block is
         * actually read.
         *
         * ⚠️ **Measured, not hypothetical.** Resolving one block's
         * settings calls `getSettings()` on EVERY contributor, because that is
         * the only way to find which one owns the key. Agent access resolves its
         * default from a `findByName` on the installer's group, so asking for the
         * PAGE CACHE's settings executed a query about chat agents -- and the
         * page cache reads its own settings on every request, cache HIT included.
         * A HIT cost 8 queries before that block existed and 9 after. This is
         * what puts it back to 8.
         *
         * Called at most once per instance. Ignored when `$defaults` is non-empty
         * so a contributor cannot half-declare both.
         *
         * @var (callable(): array<string, mixed>)|null
         */
        ?callable $lazyDefaults = null,
    ) {
        $this->declaredDefaults = $defaults;
        $this->lazyDefaults = $lazyDefaults;
    }

    /**
     * The pins actually IN FORCE, as `settings key => env var name`.
     *
     * ⚠️ **The precedence this expresses: env beats the saved row beats the
     * shipped parameter.** An operator who pins `PAGE_CACHE_TTL=0` to ride out an
     * incident must not be overridden by a row somebody saved months ago. The
     * other half of that bargain is not optional: a locked key must render
     * read-only and name its variable, because a field an admin can edit, save,
     * and watch do nothing is worse than one they cannot edit at all.
     *
     * A pin naming a variable that is not set is not a lock -- the module simply
     * declared where an operator COULD pin it.
     *
     * @return array<string, string>
     */
    public function lockedKeys(EnvironmentInterface $environment): array
    {
        $locked = [];
        foreach ($this->envPins as $key => $variable) {
            if ($environment->has($variable)) {
                $locked[$key] = $variable;
            }
        }

        return $locked;
    }
}

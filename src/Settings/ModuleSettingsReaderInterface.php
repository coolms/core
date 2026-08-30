<?php

declare(strict_types=1);

namespace CoolMS\Core\Settings;

/**
 * Reads the settings a module is ACTUALLY running on.
 *
 * The one place the platform composes an effective value. Before this existed
 * every module hand-wrote the same merge -- shipped default under saved row, key
 * by key, at request time -- and the rule was written twice over: once in PHP per
 * consumer, once in TypeScript for the admin screen. Two implementations of one
 * rule diverge where it is hardest to see, and these two already had: the PHP
 * copies type-guarded each key while the admin spread it, so a row carrying an
 * explicit `null` meant "cleared" on screen and "use the shipped value" on the
 * server. A settings screen stating something other than what is in force is the
 * failure this whole tier exists to avoid.
 *
 * A Domain contract, because a consumer is another module and a cross-module
 * Domain dependency may only be an `*Interface`.
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
interface ModuleSettingsReaderInterface
{
    /**
     * The values in force for one declared block: its module's defaults with the
     * saved row laid over them.
     *
     * ⚠️ **Merged by key PRESENCE, not by truthiness or type.** A key the admin
     * saved wins even when its value is `null` -- otherwise a field that ships
     * with a value could never be cleared, because clearing it would read back
     * as the default it was set to escape.
     *
     * Type coercion is deliberately NOT done here. The reader knows what was
     * stored; only the consumer knows what shape it needs and what an unusable
     * value should fall back to. Guarding types here would silently re-introduce
     * the divergence above, because "reject a wrong type" and "the admin cleared
     * it" would once again be the same answer.
     *
     * A block with nothing saved returns its defaults unchanged, which is what
     * the module is running on -- so a caller cannot tell a never-edited block
     * from one saved to exactly its defaults, and does not need to.
     *
     * ⚠️ **With a `$scope`, a site INHERITS the platform and overrides only what
     * it set** -- defaults, then the platform row, then that site's row. Not a
     * replacement: an operator who sets one value for one site must not silently
     * lose every platform-wide choice made around it, which is the same key-by-key
     * reasoning that governs the first two layers. Environment pins still win over
     * all three.
     *
     * `$scope` is the site's identifier, or null for the platform-wide values.
     * Passing one to a block that did not declare itself site-scopable is a
     * programming error on the WRITE side and simply resolves to the
     * platform-wide answer here -- reading can afford to be forgiving where
     * writing cannot.
     *
     * @throws UnknownSettingsKeyException when no module declared this key.
     *                                     Not an empty array: an undeclared key
     *                                     is a rename or a typo, and returning
     *                                     "no settings" would let the module run
     *                                     on defaults forever without a word
     *
     * @return array<string, mixed>
     */
    public function effective(string $key, ?string $scope = null): array;

    /**
     * The keys of one block that this deployment has PINNED, as
     * `settings key => env var name`.
     *
     * Empty for a block nobody pinned, which is most of them.
     *
     * ⚠️ Surfaced rather than silently applied. {@see effective()} already
     * ignores a saved value for a pinned key -- so without this the admin would
     * show a control, accept an edit, report a successful save, and change
     * nothing. That is the precise failure this tier exists to prevent, and
     * hiding it inside the merge would recreate it one layer down.
     *
     * @throws UnknownSettingsKeyException when no module declared this key
     *
     * @return array<string, string>
     */
    public function lockedKeys(string $key): array;
}

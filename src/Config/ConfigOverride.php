<?php

declare(strict_types=1);

namespace CoolMS\Core\Config;

use CoolMS\Core\Identifier\IdentifierProviderInterface;
use CoolMS\Core\Identifier\IdentifierProviderTrait;
use CoolMS\Core\Timestampable\TimestampableTrait;
use Symfony\Component\Uid\Uuid;

/**
 * Where config DATA lives when it cannot live in a file.
 *
 * ## Why this exists at all
 *
 * `config/modules/**` is the platform's editable config layer: git-managed,
 * shared between developers, overriding what contributors provide. Editing it
 * from the admin is only possible while the directory is writable — which is
 * true in dev and false in every deployment that ships a read-only image. This
 * table is the other half of that sentence, so the SAME admin screen works in
 * both places and neither the caller nor the feature has to know which.
 *
 * ## Keyed by (type, id), which is the whole point
 *
 * The Form module already built this — `coolms_form_config_overrides`, keyed by
 * `form_id`, with its own writer, its own overlay and its own table. It works,
 * and it is unreachable for anything that is not a form: a dashboard layout, a
 * datagrid, an editor profile would each need their own copy. `(type, id)` is
 * the key {@see ConfigLoaderInterface} already reads by, so one row shape
 * covers every kind of config data the platform has.
 *
 * Form's table is deliberately NOT migrated here. It carries a `sourceHash` and
 * a boot-time registry overlay this does not have, and rewriting a working
 * write path to prove a point is how a refactor becomes an outage — see
 * {@see \CoolMS\CoreModule\Config\ChainedConfigWriter} for what would have to
 * be reconciled first.
 */
class ConfigOverride implements IdentifierProviderInterface
{
    use IdentifierProviderTrait {
        IdentifierProviderTrait::__construct as private __identityConstruct;
    }
    use TimestampableTrait {
        TimestampableTrait::__construct as private __timestampableConstruct;
    }

    public function __construct(
        /**
         * The `type:` key — `dashboard`, `datagrid`, `navigraph`. The same
         * vocabulary a config file declares and {@see ConfigLoaderInterface}
         * matches on.
         */
        public string $configType = '',
        /**
         * The `id:` key within that type. Named `configId` because `id` is
         * already this row's own UUID — the config's identity and the record's
         * are different things, and one of the two has to say so.
         */
        public string $configId = '',
        /**
         * The whole config array, exactly as a YAML file would have held it —
         * `type` and `id` keys included, so a row read back is indistinguishable
         * from the file it replaces and nothing downstream needs a second shape.
         *
         * @var array<string, mixed>
         */
        public array $data = [],
    ) {
        $this->__identityConstruct(Uuid::v7());
        $this->__timestampableConstruct();
    }
}

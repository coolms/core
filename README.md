# coolms/core

[![CI](https://github.com/coolms/core/actions/workflows/ci.yml/badge.svg)](https://github.com/coolms/core/actions/workflows/ci.yml)
[![Latest Version](https://img.shields.io/packagist/v/coolms/core)](https://packagist.org/packages/coolms/core)
[![PHP](https://img.shields.io/badge/php-%E2%89%A5%208.5-777bb4)](https://www.php.net/releases/8.5/en.php)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

**The kernel contracts of the CoolMS platform.** Entity vocabulary,
persistence-neutral mapping attributes, domain events, and the seams every
module implements.

Four dependencies, all Symfony components. No ORM, no framework, no bundle.

```php
use CoolMS\Core\Identifier\IdentifierProviderTrait;
use CoolMS\Core\Mapping\Column;
use CoolMS\Core\Model\AggregateRootInterface;

class OutboxRecord implements AggregateRootInterface
{
    use IdentifierProviderTrait;

    public function __construct(
        #[Column(type: 'string')]
        public string $type,
    ) {}
}
```

That entity is mapped by whichever persistence adapter is installed. It names
none of them.

## Installation

```bash
composer require coolms/core
```

On its own this package gives you contracts and value objects. To persist
anything you also need an adapter — see [Persistence](#persistence).

## What is in here

| Area | What it holds |
|---|---|
| `Mapping/` | `Column`, `Id`, `GeneratedValue` — persistence-neutral attributes |
| `Identifier/`, `Timestampable/`, `Blameable/` | the reusable entity traits and their contracts |
| `Attribute/` | `ClassMeta`, `FieldMeta` — how a class describes itself to the platform |
| `Event/`, `Lifecycle/` | domain events, recorded-event providers, entity lifecycle events |
| `Outbox/`, `Inbox/`, `ChangeFeed/` | the transactional outbox, consumer idempotency, and sync change-feed rows plus their ports |
| `Config/`, `Secret/`, `Option/` | configuration loading, the secret-store contract, option sources |
| `Backup/`, `Retention/` | the backup contributor seam and the retention pruner port |

## Persistence

The mapping attributes are declarations; something has to read them. A
persistence adapter package provides
`coolms/core-persistence-implementation` and does the reading:

```bash
composer require coolms/core-doctrine
```

The attribute vocabulary is deliberately small — the columns the traits actually
use, and nothing else. It is a translation, not a second mapping layer: the
moment it grows associations or inheritance, there are two schemas to keep in
step instead of one.

## Related packages

| Package | Role |
|---|---|
| [`coolms/core-module`](https://github.com/coolms/core-module) | application services composed over these contracts |
| [`coolms/core-bundle`](https://github.com/coolms/core-bundle) | Symfony integration |
| [`coolms/core-doctrine`](https://github.com/coolms/core-doctrine) | Doctrine ORM/DBAL adapter |

## License

MIT. See [LICENSE](LICENSE).

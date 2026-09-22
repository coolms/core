# Changelog

All notable changes to `coolms/core` are recorded here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Versioning is described in `CONTRIBUTING.md` -- read it before assuming what a
major number means here.

!! Entries dated before 2026-09-01 were **reconstructed** from tags and commit
history when this file was created. Every entry after that is written in the
same commit as the change it describes.

## Unreleased

### Added
- `Messaging\RelayedOutboundInterface`, `Messaging\OutboundRelayed` and
  `Messaging\ProcessedMessageStoreInterface`: what a module says when an event
  must survive its own transaction and be relayed afterwards, what the platform
  dispatches when it has been, and the journal a consumer checks so an
  at-least-once delivery is safe to process twice. Three declarations; the
  machinery behind them -- the table, the relay, its heartbeat and the journal's
  rows -- belongs to whatever installs it, and is no longer here.
- `Ui\` -- the host-contract seam (the platform rule: hosts implement contracts, modules offer entries): `UiEntry` (what a
  module offers for a host contract: contract, `^MAJOR.MINOR` range,
  framework, entry file), `ThemeContracts` (what a theme declares it
  implements, name -> `MAJOR.MINOR`), `UiContractMatcher` (matched / unused /
  refused, with the sentence an operator reads: module, contract and range
  against theme and version), and two ports -- `UiEntryCatalogInterface` (the
  modules' `config/ui.yaml` files, read by the kernel that knows the config
  roots) and `InstalledThemeContractsInterface` (the installed themes'
  declarations, answered by the Theme module). `HostContracts` folds those
  declarations into the contracts in force: one installed theme per contract,
  a second refused by name -- "active" is the site's notion, and the console
  is served by the theme that ships it. A module never requires a contract:
  an entry no theme reads is unused; an entry a theme reads at a version the
  range does not include is refused by name.
- `Privacy\UserDataFootprint` and `Privacy\UserDataFootprintInterface`: the
  declaration a module places on the class that handles a person's data at
  account deletion -- category, label, tables, the action taken (delete,
  minimise, keep) and whether the category may be held under an obligation --
  and the marker that gets it collected. The platform's privacy guarantee rests
  on every module declaring its footprints and the deleting module reading them
  all, so the declaration is the platform's: a module at any level declares by
  importing this, and no module implements another's contract to do so. The
  holds register (the operator's obligations and durations) stays the
  reader's, named by the reader.
- `Form\FormId`, `Form\FormIdRegistryInterface` and, beside `DataGridConfig`,
  `RqlContextFactoryInterface`: the mark an API resource class carries to say
  which form renders it, the registry a manifest reads that mark back from,
  and the factory that turns a data grid declaration into the RQL context a
  list endpoint accepts. The implementations stay with the modules that own
  forms and grids; the contracts move here because the modules that mark a
  resource or serve a list sit at every level of the platform, some below the
  module that implements the contract -- a declaration to the platform is the
  platform's to own.
- `Outbox\RelayHeartbeat` and `Outbox\RelayHeartbeatInterface`: the record the
  outbox relay leaves of its own pass -- when, the batch asked for, the rows
  published, zero included -- and the port a monitor reads it through. Until
  now a relay that had stopped read exactly like one with nothing to do; an
  empty queue proves no consumer. The store behind the port has to be one the
  relay's process and the monitor's both reach.
- `Health\LivenessProbeInterface` and `Health\DependencyState`: the seam a module
  uses to declare ONE long-running dependency and the question that finds out
  whether it is alive. It sits beside `Retention\RetentionPrunerInterface` and
  `Outbox\OutboxBacklogInterface` because liveness is the same kind of thing --
  a declaration a module makes to the platform, which owns the collecting and the
  reporting while each module owns the question.
  It exists because a realtime node was dead for nine hours while a container
  lint, a smoke check, a full unit suite and a commit gate all stayed green: they
  read what the code says, and nothing asked what is running.
  `DependencyState` carries `configured` and `answered` as separate facts, so a
  dependency that is wired and dead cannot read as healthy, and carries the ASK
  itself beside the answer so a failing row says what was tried. A dependency the
  installation does not declare reports `absent`, never `ok`.
  The interface ships with ONE method and no default, deliberately: it is a
  published contract, so adding a member later would break every implementer in
  every installation. The seam grows through `DependencyState` -- a value object
  can gain an optional parameter without breaking anyone -- not through the
  interface.
- `Health\DependencyState::inconclusive()`, the fourth state, and the first time
  the seam grew the way the paragraph above says it would. It is for a question
  whose answer is compatible with a live dependency AND a dead one: an outbox
  relay over an empty outbox, a worker heartbeat nothing has dispatched.
  `status()` reports it as `unknown`; `isFailing()` does not count it. Calling it
  `ok` is the empty-queue trap -- the reasoning that makes queue depth useless
  for liveness -- and calling it `DOWN` cries wolf at every idle installation
  until the number stops being read. A doctor may say it does not know; it may
  not claim health it has not established. `DependencyStateTest` pins all four
  states and that the number counts exactly the silent required ones.

### Added
- `Outbox\OutboxBacklogInterface` and `Outbox\OutboxBacklog`: a read port for
  the undelivered half of the outbox -- unpublished rows, unpublished rows older
  than a threshold (the number that should be zero while a relay runs), and the
  oldest row's timestamp -- so a relay that stopped is visible to a monitor.

### Added

- `Analytics\ConsentCategory::Recognition` -- a second consent rung beside
  `analytics`, named for its purpose: `analytics` measures an audience over a
  reference that rotates daily, `recognition` recognises a browser across
  visits through a durable identifier issued to it. Two purposes, not two
  degrees of one. `ConsentCategory::implies()` declares the one entailment --
  recognising implies measuring -- and `ConsentCategory::closure()` applies
  it, so a canonical vector cannot say "recognition granted, analytics
  declined". The relation is declared once, here; the platform's consent
  ladder applies it where collection happens.
- `Analytics\CurrentRecognitionInterface` -- the fifth request-edge reader:
  the durable recognition id a request carries, or null. The edge only reads;
  the identifier is issued once, elsewhere, only after `recognition` is
  granted, and never computed from a request.
- `Analytics\AnalyticsEvent` gains `recognitionRef` (the durable id the row
  names, alongside the day's `visitorRef` and the signed-in `subjectRef` --
  three references, three sources, one column each) and `consentRecordId`
  (the recorded consent decision the row's vector rests on, null when the decision
  came from a cookie alone). `withRequestContext()` fills both only when
  absent; `withOnlyReferences()` returns the row with exactly the named
  references kept, which is how a consent ladder writes "everything except
  who". `consent` keeps its meaning -- what was in force at capture -- so rows
  written before decisions were recorded lose nothing and gain a null reference.
- Declares `support` -- `issues` and `source` -- so a page imported from this
  package, and the catalogue, know where a correction is filed. Packagist filled
  the gap from GitHub when the manifest was silent; the declared field is the
  one that holds on any registry.
- `Install\DeclaresPrerequisitesInterface` and `Install\InstallOrder`. An
  installer declares what it requires and what it provides, as opaque tokens
  (`system-user:admin`, `vfs-node:/`), and `InstallOrder::sort()` derives the
  run order from the declarations. It refuses -- `UnorderableInstallersException`,
  before anything runs -- when a requirement has no provider or the declarations
  form a cycle, naming every unsatisfied token and every member of the cycle
  rather than the first. Opt-in per installer: a non-declarer is placed by the
  class-name tie-break, which is the order it always had.
- `InstallOrder::sort()` takes a second argument, the tokens an EARLIER phase
  provided, and `InstallOrder::provisionsOf()` collects them. `coolms:install`
  sorts its structure and module phases separately, and an installer in the
  second may require what the first provided; sorting the second alone refused
  on a fact. Two of the tests are the case and its control.

- `Identity\ElevationInterface` and `Identity\ElevationGateInterface`, with
  `Identity\MembershipBypassGate` as the default implementation of the gate.
  Membership of the administrators' group is who you are; elevation is what
  state you are in: temporary, self-obtained, expiring on its own. Every privileged gate asks
  `mayBypass()` instead of reading `$isAdmin` for itself, so the answer can move
  from membership to elevation in one place and -- the reason it is a port --
  can be rehearsed: a recording implementation counts EVALUATIONS beside
  would-refuse HITS per gate, so "no bypass observed" and "the gate never ran"
  are different outputs. The default is the pre-existing behaviour, membership,
  uncounted, so hand-built fixtures keep constructing; the container wires the
  recording one. `Identity\ElevationShadowStoreInterface` is where a recording
  gate writes and the confirmation report reads: the writer and the reader are
  different modules, and neither may import the other.

Tests the application had been carrying for this package since the code
moved here: `ContentSeederTest`, `SeedGuardTest`, `ModuleSpaceSettingsTest`. Nothing under `src/` changes.

**The master key ring, as a contract.** `Secret\MasterKeyRingInterface` is
the set of at-rest master keys a host holds, in the order a reader tries them:
the current key, then -- during a rotation window -- the previous one. Writes
use the current key, always; reads try both, which is what makes a half-finished
rotation readable in every kind. `Secret\MasterKey` carries the 32 bytes and a
derived id (16 hex of the SHA-256), so a sealed value can name the key it was
sealed under and every host computes the same name. `Secret\SealedKindInterface`
is one kind of sealed value as the rotation sweep sees it -- a name and an
idempotent sweep -- with `Secret\RotationTally` for what the sweep found:
under the current key, under the previous key, under neither, not sealed at
all, re-sealed. `MasterKeyException` and `SealedValueException` name the two
failures apart: a key that is unusable, and a value that no held key opens.
The implementations live in `coolms/core-bundle`.

### Changed

- `Identity\UserInterface::$isRoot` is `$isAdmin`. There is one privilege tier
  above a signed-in user, `ROLE_ADMIN`, and the property is named for it. The
  application it was extracted from renamed the system user `root` to `admin`
  and folded `ROLE_ROOT` into `ROLE_ADMIN` on the same day; a consumer with a
  `root` account is not affected by this package, only by that application.
- `Install\VfsInstallerInterface`'s docblock now describes the order an
  installer actually gets: registration order (alphabetical) unless it
  declares, and the derived order when it does. It used to promise a
  dependency order nothing implemented.

### Removed
- The `Outbox` and `Inbox` namespaces: the message value and record, the
  appender, publisher, backlog and relay-repository ports, the relay
  heartbeat and the dedupe record. A platform contract says what a module
  declares; these said how one installation stores and relays it, which is an
  installation's own affair. What a module needs is in `Messaging` above.

- 77 classes that exactly one module of the application used, moved into
  that module: the Decision DMN AST, engine results, exceptions and
  validation contract (20); the Word block model and Tiptap mapping
  contracts (11); the Form field-item definitions and the YAML, PHP and XML
  config writers (8); the Theme content seeder, its guard and run (5);
  Content's block enums and space-provider contracts (4); Media's focal
  point, thumbnail config and two contracts (4); VFS's mime registries,
  renderable-engine contract and `ByteFormatter` (4); the Analytics
  segment-transition event and subject-segments reader (3); the Email OAuth
  contract, tokens and exception (3); the Scheduler trigger contract, kind
  and exception (3); and two each for Document, Editor, Mcp, Web and the
  Workflow validation contract, Navi's reorder event, and Settings' writer
  contract. A consumer that imported one of them from `CoolMS\Core` imports
  it from the module's Domain now. The contracts other modules implement stay
  here, and so does every type their signatures name:
  `Seed\SeedTargetInterface`, the mime provider contracts,
  `Form\ConfigWriterInterface`, `Form\FieldItemAdapterInterface`,
  `Settings\ModuleSettingsReaderInterface` and its exception,
  `Mail\Exception\MailCompositionException` (the `@throws` of the rich-mail
  contracts).
- Eight classes nothing called: `Exception\ReadonlyPropertyException`,
  `Exception\TranslatableExceptionTrait` (an implementation holds the triple
  itself), the five lifecycle events never dispatched (`PreSaveEvent`,
  `PostSaveEvent`, `PreDeleteEvent`, `PostDeleteEvent`, `OnDeleteEvent`;
  `OnCreateEvent` and `OnUpdateEvent` stay), and
  `Workflow\AbstractWorkflowAstVisitor`.
- Nine shared classes placed by the rule "a type in a contract's signature
  goes with the contract's owner; anything else to the module whose concept
  it is": the front-end stack enums (`Enum\FeStackType`, `SpaFramework`,
  `HybridFramework`, `InertiaAdapter`) to the Section module;
  `Event\NotificationRequested` to Notification; `Event\StartWorkflowRequested`
  to Workflow; `Space\ModuleSpaceSettings` to Settings;
  `VFS\FileKindProviderInterface` and `VFS\CreatableFileKind` to VFS, which
  collects the providers. The shared classes that stay do so by the same
  rule: the base domain event and `RecordedEventsTrait`, `LocalizedText`,
  `NaviNodeDefinition`, `ModuleSettingsDefinition`, the timestamp and
  blameable traits, the installer and API-resource and outbound-channel
  types, and every type a contract that stays names.
- Fifteen more shared classes placed with their owners once the rule that
  refused a concrete Domain class across the same level was retired (a Domain
  class is a Domain class; at package level an interface and a class are the
  same dependency): `Editor\EditorPanel` to Editor; `Identity\MembershipBypassGate`
  and `Identity\EntityWalkUserGroupResolver` to Identity (their contracts,
  `ElevationGateInterface` and `UserGroupResolverInterface`, stay); the
  definition catalog and lifecycle contracts with their types
  (`DefinitionCatalogProviderInterface`, `DefinitionCatalogFilter`,
  `DefinitionCatalogRow`, `DefinitionLifecycleProviderInterface`,
  `DefinitionLifecycleRefused`, `DefinitionNotFound`) to Definition, which
  collects them; `Form\DataSourceResolverInterface`, `DataSourceDefinition` and
  `DataSourceOption` to Form; `Link\LinkTargetResolverInterface`, `LinkTarget`
  and `ResolvedLink` to Link. Each collecting module registers the contract for
  autoconfiguration itself.

## 2.0.0-alpha3 - 2026-09-09
### Added

- `Template\ContextContributorInterface`, moved down from the application tier.
  Three contracts here extended the application's copy while that package
  required this one back -- the one dependency shape declaring cannot repair,
  because the declaration writes the cycle down instead of removing it. The old
  name survives there as a subtype with nothing added, so consumers typed
  against either still compile.
- The extension-point contracts a module implements: 44 seams that could
  previously only be implemented from inside an application, with the value
  objects and enums their signatures name -- 160 types. Which seams was asked of
  the container rather than of a naming convention: every type passed to
  `registerForAutoconfiguration()` is an extension point by definition.
- `ApiDescription` as a core contract. An attribute defined in an application
  can only ever describe that application's own routes, so a package shipping
  routes of its own had no way to describe them at all.
- The per-site space convention. `ModuleSpaceSettings` answers "which sites is
  this module turned on for" once for every module, leaving only the
  module-specific half -- what to create when a space is enabled -- to
  `SpaceProvisionerInterface`.
- The seeding guard and the block-type vocabulary, which application code and
  its tests already depended on.
- `BlockWidth` (full, two-thirds, half, third, quarter) and `BlockAlign` (top,
  middle, bottom, stretch), the horizontal and vertical halves of the block
  vocabulary, plus `BlockField::KIND_CHOICE`. Names rather than column counts,
  so the vocabulary means the same thing in a theme built on Bootstrap, on CSS
  grid, or on nothing.
- `ReservedFieldNames` and its exception, which sat in a module that never
  referenced them while their only consumer read them across a boundary.

### Changed

- A seed records which `extras` keys it wrote. The guard compared bodies only,
  so a landing page whose blocks an editor had rearranged still matched its
  recorded body hash, was called unedited, and had the arrangement overwritten.
- `coolms/rql` is declared. It was already reached from two public grid
  signatures; the dependency was correct and only the declaration was missing.
- Comments, docblocks and changelogs are ascii and no longer cite internal slice
  or ticket ids.
- Development-only files are export-ignored, so `composer require` no longer
  downloads them.

!! **Release this with the rest of generation 2.** Nothing in `CoolMS\Core\`
moved -- the domain package keeps the root prefix -- but the tiers above it were
renamed and their namespaces nested in this same generation.

## 2.0.0-alpha2 - 2026-09-03

### Added

The contracts module removal needs, which have been on `develop` since
2026-09-02 and were not in a tag:

- `Install\ModuleUninstallerInterface` -- what a module undoes when it is
  removed, and the name it is known by.
- `Install\ModuleNavigationRemoverInterface` -- the seam through which a
  navigation module withdraws the nodes an installation seeded. Whoever
  manages navigation implements it; nothing in this package does.
- `Install\PostInstallNoop` -- the no-op `postInstall()` the
  `ModuleInstallerInterface` docblock had been promising without providing.

!! **Release this whenever you release `coolms/core-bundle`.**
`ModuleArtifactRemover` in core-bundle 2.0.0-alpha2 requires
`ModuleNavigationRemoverInterface` by type. Pairing core-bundle 2.0.0-alpha2
with core 2.0.0-alpha1 gives a container that does not compile, and Composer
reports no conflict because `^2.0` admits both. That combination existed on
Packagist for roughly twenty minutes on 2026-09-03; this release ends it.
## 2.0.0-alpha1 - 2026-09-01

**A pre-release. It carries no compatibility promise**, which is the honest
statement of where the platform is: the shape is still moving, and a stable tag
would be a promise that cannot be kept yet.

Composer will not install it under default stability. Set

```json
"minimum-stability": "alpha",
"prefer-stable": true
```

in your root `composer.json`, then:

```
composer require coolms/core:^2.0
```

`prefer-stable` keeps every other dependency of yours on its newest stable
release, so this loosening applies to what actually needs it and nothing else.

This package requires no siblings, so `composer require coolms/core:^2.0@alpha`
also works. !! That shortcut does **not** carry to the packages built on it: a
stability flag applies to the package it names and not to what that package
requires in turn.

A bare `composer require coolms/core` resolves **successfully** to v1.0.0 --
the previous generation -- and reports success while doing it.

Releases are suspended while development is moving fast and there are no
external consumers of these packages. This tag establishes the baseline the
documentation describes; nothing follows it until somebody outside the project
installs one, at which point the release policy resumes.

### Added: the contracts a module implements from outside the application

Seven classes move here from the application so a package outside this tree can
name them: `Theme\ThemeProviderInterface`, `Theme\ThemeAssetsProviderInterface`
and `Theme\ThemeAssets`; `Identity\SystemUserProviderInterface` and
`Install\VfsInstallerInterface`, the first two seams published on request; and
`Install\DeclaresVfsPathsInterface` with `Install\VfsPathClaims`, by which an
installer states the VFS paths it claims and `coolms:install` reports two modules
claiming one.

A contract published at one end only is a seam nobody outside can reach: the DI
tag existed for each of these, the interface did not.

### The module-settings contract, so any module can declare one

`CoolMS\Core\Settings` -- `ModuleSettingsContributorInterface`,
`ModuleSettingsDefinition`, `ModuleSettingsReaderInterface` and
`EnvironmentInterface`. A module declares the settings blocks it owns by
implementing the contributor; the platform composes what a module ships, what an
operator saved, and what one site overrode, with environment variables beating
all three.

These interfaces lived in the application's own Settings module until now, and
that placement quietly decided who could use them. Module boundaries there bar a
module from importing a *sibling's* domain types, and Settings sits at the
foundation level -- so every module beside it (realtime transport, VFS,
identity, i18n, forms, sections, taxonomy) could **read** a setting, because an
interface is allowed across that line, but could not **declare** one, because
the definition is a concrete type. The result was a platform whose upper layers
were configurable and whose foundation was not, and nothing surfaced it until a
module at that level tried.

Core is the one place every module may depend on, so the contract belongs here.

**Upgrading: nothing to do unless you implemented these yourself.** They were
not part of any published Core release, so no published constraint can break.
An application that declared its own copies should point its imports at
`CoolMS\Core\Settings\` and delete them; the shapes are unchanged.

### The v2 generation -- a version number, and nothing else

This release moves `coolms/core` to `2.0.0` **without a single change to its
code**. Nothing was added, removed, renamed or fixed.

Every CoolMS platform package -- everything that requires `coolms/core` --
shares a major number, so that a set of packages carrying the same major is
known to work together. The whole set crosses to v2 at once, and this package
has nothing else in the crossing.

Before the shared major existed, `composer require coolms/entity-bundle`
resolved the entire set backwards onto its first generation -- including a
template engine from before output encoding existed -- and Composer reported
success. A shared major makes that resolution unreachable by accident.

**Upgrading: widen your constraint from `^1.0` to `^2.0`. There is nothing
else to do.** No class, signature, or behaviour changed. Breaks are announced as
deprecations in a minor and removed at a generation boundary; this boundary
removes none, because there were none to remove.

The standalone libraries published alongside the platform -- `coolms/rql`,
`coolms/rql-doctrine`, `coolms/dtmpl`, `coolms/dtmpl-bundle` -- do **not** take
this major. They have users who never touch CoolMS, and their numbers answer to
their own APIs.

## 1.0.0 - 2026-08-17

First release. The kernel contracts every other CoolMS package builds on: the
entity vocabulary, the persistence-neutral mapping attributes, domain events,
config loading, the outbox and inbox seams, and the interfaces modules
implement.

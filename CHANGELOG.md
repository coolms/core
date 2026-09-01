# Changelog

All notable changes to `coolms/core` are recorded here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Versioning is described in `CONTRIBUTING.md` -- read it before assuming what a
major number means here.

⚠️ Entries dated before 2026-09-01 were **reconstructed** from tags and commit
history when this file was created. Every entry after that is written in the
same commit as the change it describes.

## 2.0.0-alpha1 - 2026-09-01

**A pre-release. It carries no compatibility promise**, which is the honest
statement of where the platform is: the shape is still moving, and a stable tag
would be a promise that cannot be kept yet.

Composer will not install it under default stability. Set

```json
"minimum-stability": "alpha",
"prefer-stable": true
```

in your root `composer.json`, then `composer require coolms/core:^2.0`.
`prefer-stable` keeps every other dependency of yours on its newest stable
release, so this loosening applies to what actually needs it and nothing else.

This package requires no siblings, so `composer require coolms/core:^2.0@alpha`
also works. ⚠️ That shortcut does **not** carry to the packages built on it: a
stability flag applies to the package it names and not to what that package
requires in turn.

A bare `composer require coolms/core` takes the newest **stable** release
instead -- which is the previous generation -- and reports success while doing
it.

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

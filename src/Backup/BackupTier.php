<?php

declare(strict_types=1);

namespace CoolMS\Core\Backup;

/**
 * Classifies the KIND of data a {@see BackupContributorInterface} owns, so a
 * backup run can be scoped to a use case:
 *
 *  - {@see self::Config}  — platform/setup data you author (definitions, nav,
 *    sections, calendars, forms, field defs). The "deploy my setup to a fresh
 *    remote" payload.
 *  - {@see self::Content} — authored content + its binaries (VFS Node tree +
 *    blobs: pages, media, documents). Ships with Config for a deploy.
 *  - {@see self::Runtime} — transient/operational data (analytics events, chat
 *    history, workflow process instances, run logs, sessions/tokens). Only
 *    wanted for a full disaster-recovery clone, NOT a config deploy.
 *
 * The default `coolms:backup:create` profile is `Config + Content` (the
 * local→remote deploy case); `--tier=all` adds `Runtime`. A module with data in
 * more than one tier registers MORE THAN ONE contributor (exactly as a module
 * can register several retention pruners), one per tier.
 */
enum BackupTier: string
{
    case Config = 'config';
    case Content = 'content';
    case Runtime = 'runtime';
}

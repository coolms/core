<?php

declare(strict_types=1);

namespace CoolMS\Core\Site;

/**
 * The installation's application base URL: where its admin and workspace answer, and what every absolute link is built
 * from when nothing else names a host -- a message rendered in a worker, a console command, a link built with no
 * request. An explicit setting of the installation, made once and changed only by an elevated administrator; never
 * taken from a request's Host header and never guessed.
 *
 * Read-only: setting it is the settings tier's business, not a consumer's.
 *
 * !! **Unset is an answer, not a default.** Until the setting is made, {@see baseUrl()} answers null, and a consumer
 * that needs it refuses with an error that says the setting is missing -- it never falls back to `localhost`, the
 * router's default URI or the request it happens to be in.
 */
interface ApplicationBaseUrlInterface
{
    /**
     * The base URL, `https://host` or `scheme://host:port`, with no path and no trailing slash; null while unset.
     */
    public function baseUrl(): ?string;
}

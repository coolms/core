<?php

declare(strict_types=1);

namespace CoolMS\Core\Ui;

/**
 * What the matcher says about one entry against one theme.
 *
 * `Unused` is not a failure: the theme reads no such contract, or reads it on
 * another framework, and the module installs with the entry dormant. `Refused`
 * is: the theme reads the contract at a version the entry's range does not
 * include, and installing would leave a declared entry the host cannot mount.
 */
enum UiVerdict: string
{
    case Matched = 'matched';
    case Unused = 'unused';
    case Refused = 'refused';
}

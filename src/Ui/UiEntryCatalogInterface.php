<?php

declare(strict_types=1);

namespace CoolMS\Core\Ui;

/**
 * Every entry the installed modules offer, read from their `config/ui.yaml`
 * (`config/modules/<id>/ui.yaml` in the application, `config/ui.yaml` in a
 * package). The reader lives with the kernel that knows the config roots;
 * the callers of {@see UiContractMatcher} depend on this.
 */
interface UiEntryCatalogInterface
{
    /**
     * @return list<UiEntry>
     */
    public function entries(): array;
}

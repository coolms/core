<?php

declare(strict_types=1);

namespace CoolMS\Core\Timestampable;

trait TimestampableTrait
{
    use CreatedAtProviderTrait {
        CreatedAtProviderTrait::__construct as private __createableTraitConstruct;
    }
    use UpdatedAtProviderTrait {
        UpdatedAtProviderTrait::__construct as private __updateableTraitConstruct;
    }
    use AccessedAtProviderTrait {
        AccessedAtProviderTrait::__construct as private __accessibleTraitConstruct;
    }

    public function __construct()
    {
        $this->__createableTraitConstruct();
        $this->__updateableTraitConstruct();
        $this->__accessibleTraitConstruct();
    }
}

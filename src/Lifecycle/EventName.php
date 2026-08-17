<?php

declare(strict_types=1);

namespace CoolMS\Core\Lifecycle;

enum EventName: string
{
    case ON_CREATE = 'entity.onCreate';
    case ON_UPDATE = 'entity.onUpdate';
    case ON_DELETE = 'entity.onDelete';
    case PRE_SAVE = 'entity.preSave';
    case POST_SAVE = 'entity.postSave';
    case PRE_DELETE = 'entity.preDelete';
    case POST_DELETE = 'entity.postDelete';
}

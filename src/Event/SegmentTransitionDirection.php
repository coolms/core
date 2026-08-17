<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

/**
 * Direction of a CDP segment-membership change carried by
 * {@see SubjectSegmentTransitioned} (Track E Phase 5, activation substrate).
 *
 * `Entered` is the primary activation trigger (a subject just joined an
 * audience → start a journey / fan out / push); `Exited` supports the mirror
 * (stop a journey / retract). The string value doubles as the low-cardinality
 * `direction` dimension on the aggregate `segment.transition` analytics event.
 */
enum SegmentTransitionDirection: string
{
    case Entered = 'entered';
    case Exited = 'exited';
}

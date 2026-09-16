<?php

declare(strict_types=1);

namespace CoolMS\Core\Analytics;

/**
 * Request-edge reader for the durable recognition identifier this request
 * carries -- the fifth "Current*" reader beside
 * {@see CurrentVisitorReferenceInterface}, {@see CurrentRequestDimensionsInterface},
 * {@see CurrentConsentInterface} and {@see CurrentSubjectSegmentsInterface}.
 *
 * !! THE EDGE ONLY READS. Where the daily {@see VisitorReferenceGeneratorInterface}
 * reference is COMPUTED per request from what the request carries, a durable
 * identifier cannot be computed: it is ISSUED, once, in one place, only after
 * the `recognition` consent category has been granted -- and issuing it changes
 * browser state, which is a different kind of act from reading a header. This
 * reader therefore answers null when the request carries no identifier, stamps
 * nothing, and never creates one. An identifier issued earlier in the same
 * request (by the one issuing point) is visible through it for the rest of that
 * request, so producers after the issuance stamp the new id.
 *
 * Lives in Core (L0) so producers at any level can read it; the Consent module
 * implements it over the cookie the issuing point writes.
 */
interface CurrentRecognitionInterface
{
    /** The durable recognition id this request carries, or null -- never minted here. */
    public function current(): ?string;
}

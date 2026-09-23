<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

/**
 * A {@see SealedKindInterface} that also answers the second question: how many
 * copies of its ciphertext does the estate still hold that the ring cannot
 * open?
 *
 * **Why it is a separate interface and not two methods on the kind.** Not every
 * kind has residue, and the difference between "this kind keeps no copy" and
 * "nobody has asked this kind" must survive into the report. A kind that does
 * not implement this reports NOT ASKED -- never zero. An unimplemented port
 * answering zero is the same defect as a completion count that is true about
 * what it did and silent about what it left, one level up, and it is the one
 * that would make the whole seam worthless: the rotation would print a green
 * assembled from silence.
 *
 * **What a residue is.** A rotation re-seals VALUES -- what the application
 * reads and writes through its own read path. A copy is the same ciphertext
 * kept elsewhere by a subsystem with its own reasons: a content-addressed blob
 * a file-history revision still references, an append-only log, a derived
 * artefact. Those copies survive the rotation, and a retired key opens every
 * one of them.
 *
 * **One method, and it stays one method.** This is a published package: adding
 * a member breaks every implementer in every installation on upgrade. New facts
 * about residue belong on {@see ResidueTally}, which can gain an optional
 * member or a named constructor without breaking anyone.
 *
 * ## What an implementation must do
 *
 * - **Count by opening.** Ask the ring. The stored form names its key and that
 *   id is a hint, not proof; a marker may narrow the candidates and the box
 *   decides. A marker-only count is a count of claims.
 * - **Earn its zero.** Report the denominator. `0 of 0` establishes nothing,
 *   and {@see ResidueTally::noCopyKept()} exists so that a kind with nothing to
 *   walk says exactly that instead of dressing it as a counted zero.
 * - **Say what it cannot see.** Every tally carries a blind spot. A count over
 *   a byte store does not see a database dump or a bundle on removable media.
 * - **Write nothing.** This is a question. Collecting what it finds is the
 *   owning subsystem's act, on an operator's word.
 * - **Stay bounded.** It runs inside the emergency procedure, beside the sweep
 *   whose memory and wall time are already reported per kind.
 */
interface SealedResidueInterface
{
    public function residue(): ResidueTally;
}

<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * BPMN **multi-instance** marker on an activity: run it once per item
 * of a collection.
 *
 * **Sequential only, and `parallel` is REJECTED at deploy** rather than
 * quietly run one-at-a-time. Parallel multi-instance needs each
 * concurrent iteration to see a DIFFERENT value under
 * {@see $elementVariable}, which means per-TOKEN variable state — the
 * engine's variables live on the ProcessInstance, shared by every token
 * in it. Running a `parallel` declaration sequentially would produce
 * the right final answer with the wrong semantics (no concurrency, and
 * a completion condition that behaves differently), so the honest move
 * is a violation with a code the author can act on. See
 * `MultiInstanceRule`.
 *
 * `collection` and `completionCondition` are EL evaluated against the
 * instance's variables; `elementVariable` is the variable NAME the
 * current item is written to before each iteration.
 */
final readonly class LoopCharacteristics
{
    public function __construct(
        /**
         * EL yielding the collection to iterate. Anything countable +
         * iterable; a null / empty result skips the activity entirely
         * (BPMN's "zero instances" case), which is why the engine
         * evaluates it BEFORE entering the activity.
         */
        public ConditionExpression $collection,
        /**
         * Instance-variable name the current item is written to before
         * each iteration. Defaults to `item` so a body can be authored
         * without naming it.
         */
        public string $elementVariable = 'item',
        /**
         * Optional EL re-evaluated after each iteration. When it becomes
         * true the loop stops EARLY and the token advances — BPMN's
         * "good enough, stop asking" (e.g. one approval out of five).
         */
        public ?ConditionExpression $completionCondition = null,
        /**
         * Author-declared, and carried even though only `false` runs.
         * Kept on the VO rather than dropped at parse so the validator
         * can name what it is rejecting, and so a future parallel
         * implementation reads the same field instead of inventing one.
         */
        public bool $parallel = false,
    ) {
    }
}

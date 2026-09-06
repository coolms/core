<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

/**
 * Variant axis for {@see \CoolMS\Core\Workflow\EndEventAst}.
 *
 * - `none`       — the token simply ends; the instance completes once no
 *                  other Active/Waiting tokens remain.
 * - `terminate` — a FATAL end: reaching it kills
 *                  every remaining token in the instance and completes the
 *                  instance immediately, regardless of other in-flight
 *                  branches. Composes with error boundaries (a service-task
 *                  error → error boundary → terminate end = abort the whole
 *                  process). Handled by `TokenAdvancer` in the consuming application.
 * - `compensate` — a compensation THROW end: reaching
 *                  it runs the compensation handlers of every completed
 *                  compensable activity in the instance, in REVERSE completion
 *                  order (LIFO undo), then completes this token like a normal
 *                  end. The saga-rollback leg an error-boundary recovery path
 *                  routes into. Handled by `TokenAdvancer` in the consuming application.
 *
 * Error / escalation / cancel / signal end events stay deferred (some not
 * planned; see m2c-design §5.5(a)).
 */
enum EndEventVariant: string
{
    case None = 'none';
    case Terminate = 'terminate';
    case Compensate = 'compensate';
}

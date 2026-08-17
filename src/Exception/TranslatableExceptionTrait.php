<?php

declare(strict_types=1);

namespace CoolMS\Core\Exception;

/**
 * Convenience implementation of {@see TranslatableExceptionInterface}.
 * F6 Phase 3.
 *
 * Holds the translation triple and exposes the getters; the concrete
 * exception calls {@see setTranslation()} from its constructor or a
 * named factory, after the parent `\Exception` constructor has set the
 * raw (developer-facing) message:
 *
 * ```php
 * final class NotForkableException extends \RuntimeException
 *     implements TranslatableExceptionInterface
 * {
 *     use TranslatableExceptionTrait;
 *
 *     public static function contributorLocked(string $key): self
 *     {
 *         $e = new self(sprintf('Definition "%s" is module-locked.', $key));
 *         $e->setTranslation('errors.definition.not_forkable', ['%key%' => $key], 'exceptions');
 *         return $e;
 *     }
 * }
 * ```
 *
 * An exception that uses the trait but never calls `setTranslation()`
 * reports an empty key -- the renderer treats that the same as "no
 * translation" and uses the raw message, so partial adoption is safe.
 */
trait TranslatableExceptionTrait
{
    private string $translationKey = '';

    /** @var array<string, mixed> */
    private array $translationParameters = [];

    private ?string $translationDomain = null;

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }

    /**
     * @return array<string, mixed>
     */
    public function getTranslationParameters(): array
    {
        return $this->translationParameters;
    }

    public function getTranslationDomain(): ?string
    {
        return $this->translationDomain;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function setTranslation(string $key, array $parameters = [], ?string $domain = null): void
    {
        $this->translationKey = $key;
        $this->translationParameters = $parameters;
        $this->translationDomain = $domain;
    }
}

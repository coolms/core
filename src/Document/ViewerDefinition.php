<?php

declare(strict_types=1);
namespace CoolMS\Core\Document;

/**
 * Declarative viewer definition contributed by a module. The frontend's
 * `ViewerHostComponent` resolves a file's MIME type to a definition,
 * looks up `component` in `ViewerComponentRegistry`, and renders the
 * resolved Angular component with the matching profile config injected.
 */
final readonly class ViewerDefinition
{
    /**
     * @param list<string>                 $mimeTypes
     * @param list<string>                 $extensions
     * @param array<string, ViewerProfile> $profiles   keyed by profile name
     *                                                 (`default`, `compact`, `preview`)
     */
    public function __construct(
        public string $key,
        public array $mimeTypes,
        public array $extensions,
        public string $component,
        public array $profiles,
    ) {
    }

    /**
     * @return array{
     *     key: string,
     *     mimeTypes: list<string>,
     *     extensions: list<string>,
     *     component: string,
     *     profiles: array<string, array{key: string, config: array<string, mixed>}>,
     * }
     */
    public function toArray(): array
    {
        $profiles = [];
        foreach ($this->profiles as $name => $profile) {
            $profiles[$name] = $profile->toArray();
        }

        return [
            'key' => $this->key,
            'mimeTypes' => $this->mimeTypes,
            'extensions' => $this->extensions,
            'component' => $this->component,
            'profiles' => $profiles,
        ];
    }
}

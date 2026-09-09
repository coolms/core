<?php

declare(strict_types=1);
namespace CoolMS\Core\Web;

/**
 * One classified slice of source code produced by a {@see GrammarInterface}.
 *
 * `$scope` is the highlight.js token vocabulary (e.g. `keyword`, `string`,
 * `comment`, `number`, `built_in`, `variable`, `literal`, `meta`, `symbol`) so
 * the server-emitted `<span class="hljs-{scope}">` matches the theme's existing
 * `pre code .hljs-...` palette and the admin editor's colours -- authoring and
 * published output stay identical. An empty `$scope` is plain, un-coloured text.
 *
 * `$text` is the RAW source slice (never pre-escaped); {@see SyntaxHighlighter}
 * owns the single HTML-escaping pass so grammars cannot double-escape or leak
 * markup.
 */
final readonly class HighlightToken
{
    public function __construct(
        public string $scope,
        public string $text,
    ) {
    }

    public static function plain(string $text): self
    {
        return new self('', $text);
    }
}

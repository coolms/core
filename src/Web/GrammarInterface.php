<?php

declare(strict_types=1);
namespace CoolMS\Core\Web;

/**
 * A forgiving source-code tokenizer for one or more languages, used for
 * server-side syntax highlighting of published content code blocks.
 *
 * Contract: implementations MUST NOT throw on any input — a code sample may be
 * a deliberately-broken or partial snippet — and MUST return the input's bytes
 * exactly once across all token `$text` values (concatenating the tokens
 * reproduces the source). Classification is best-effort; unrecognised spans are
 * emitted as plain {@see HighlightToken} (empty scope).
 *
 * Implementations are collected by `SyntaxHighlighter` in the consuming application
 * via the `coolms.web.highlight_grammar` tag, so any module can contribute a
 * grammar for its own DSL without touching the highlighter.
 */
interface GrammarInterface
{
    /**
     * Lowercase language ids/aliases this grammar can tokenize (e.g.
     * `['javascript', 'js', 'jsx']`). Used to build the language → grammar map.
     *
     * @return list<string>
     */
    public function languages(): array;

    /**
     * Tokenize `$code` for `$language` (one of {@see languages()}). Never throws.
     *
     * @return list<HighlightToken>
     */
    public function tokenize(string $code, string $language): array;
}

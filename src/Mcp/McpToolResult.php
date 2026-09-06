<?php

declare(strict_types=1);
namespace CoolMS\Core\Mcp;

/**
 * The result of an MCP `tools/call` (ADR-147) — a `content` array plus the
 * `isError` flag, matching the MCP tool-result shape.
 *
 * Per the MCP spec a tool's OWN failure is a normal result with `isError: true`
 * (so the model can see + react to it), distinct from a protocol-level JSON-RPC
 * error (unknown method / bad params). `McpServer` in the consuming application
 * wraps a thrown tool into {@see error()}; tools return {@see text()} on success.
 */
final readonly class McpToolResult
{
    /**
     * @param list<array<string, mixed>> $content MCP content blocks (e.g. `[['type' => 'text', 'text' => '…']]`)
     */
    private function __construct(
        public array $content,
        public bool $isError,
    ) {
    }

    /**
     * A successful single text-content result.
     */
    public static function text(string $text): self
    {
        return new self([['type' => 'text', 'text' => $text]], false);
    }

    /**
     * A tool-execution error carried as a result (`isError: true`), not a
     * protocol error.
     */
    public static function error(string $text): self
    {
        return new self([['type' => 'text', 'text' => $text]], true);
    }

    /**
     * An arbitrary content-block result (for tools returning richer blocks).
     *
     * @param list<array<string, mixed>> $content
     */
    public static function of(array $content, bool $isError = false): self
    {
        return new self($content, $isError);
    }

    /**
     * The JSON-RPC `result` payload for a `tools/call`.
     *
     * @return array{content: list<array<string, mixed>>, isError: bool}
     */
    public function toArray(): array
    {
        return ['content' => $this->content, 'isError' => $this->isError];
    }
}

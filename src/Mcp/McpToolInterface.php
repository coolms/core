<?php

declare(strict_types=1);
namespace CoolMS\Core\Mcp;

/**
 * One governed MCP tool -- a self-describing capability an external AI
 * agent can discover via `tools/list` and invoke via `tools/call`.
 *
 * Impls are auto-tagged `coolms.mcp.tool` (see the Mcp Extension) and keyed into
 * `McpToolRegistry` in the consuming application by {@see $name} at compile time by
 * `McpToolRegistryPass` in the consuming application.
 *
 * **`$name` is a virtual property hook returning a literal.** The registry pass
 * reflects it on a constructor-less stub (`newInstanceWithoutConstructor`) so a
 * duplicate-name clash fails the build; the hook must therefore read only a
 * literal, never `$this->...` constructor state (same contract as
 * `ServiceTaskHandlerInterface::$key`).
 *
 * Each tool is an adapter over an EXISTING module port (content search, workflow
 * start, VFS read...) -- the `Mcp` module owns no domain model of its own, per
 * Keep tool constructors lazy-friendly: the registry only materialises
 * a tool on first lookup, so a heavy capability is never built until called.
 */
interface McpToolInterface
{
    /**
     * Stable, dotted tool name (e.g. `server.info`, `content.search`). The
     * registry key + the `tools/call` selector. MUST be a virtual get-hook
     * returning a literal (reflected without the constructor).
     */
    public string $name { get; }

    /**
     * Short human-facing label for the tool (MCP `title`).
     */
    public function title(): string;

    /**
     * One-line description shown to the model so it can decide when to call the
     * tool. Write it for an LLM audience.
     */
    public function description(): string;

    /**
     * JSON-Schema object describing the tool's `arguments` (MCP `inputSchema`).
     * A no-argument tool returns `['type' => 'object', 'additionalProperties' => false]`.
     *
     * @return array<string, mixed>
     */
    public function inputSchema(): array;

    /**
     * The role a caller must hold to SEE and CALL this tool, or null when any
     * authenticated caller may use it. Enforced by `McpServer` in the consuming application
     * via the platform authorization checker -- the "governed tools" gate.
     */
    public function requiredRole(): ?string;

    /**
     * Execute the tool. A thrown exception is surfaced to the model as an
     * `isError` result (not a protocol error), so return normally and let the
     * server wrap failures.
     *
     * @param array<string, mixed> $arguments the caller-supplied arguments (already an array)
     */
    public function invoke(array $arguments): McpToolResult;
}

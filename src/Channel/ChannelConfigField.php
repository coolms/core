<?php

declare(strict_types=1);

namespace CoolMS\Core\Channel;

/**
 * One setting an outbound channel needs before it can deliver.
 *
 * The point of declaring these rather than hard-coding them in the admin: the
 * per-section dialog used to carry a bespoke "Webhook URL" input, so a channel
 * that needed anything else — a chat provider's bot token and room id, WebSub's hub —
 * had nowhere to put it and soft-skipped forever. A channel that declares its
 * fields gets an editor for free, in Connector or in a module the platform has
 * never heard of.
 *
 * Deliberately a SMALL vocabulary. This describes what a channel needs, not how
 * an admin should look: `type` is the coarse input kind, and anything richer
 * belongs in the channel's own settings screen rather than in a per-section
 * config row.
 */
final readonly class ChannelConfigField
{
    /**
     * The value is the NAME of a secret, not the secret.
     *
     * There is deliberately no "raw credential" field kind. Per-section config
     * is persisted in the collection Node's `extras` — plain JSON readable by
     * anyone who can read the section — so a field carrying a live token there
     * would be a credential in content metadata no matter how the admin renders
     * it. An earlier design shipped a `secret: bool` flag meaning "render a
     * password box and never echo it", which hid that problem behind a UI
     * affordance; this
     * replaces it, because the safe path has to be the only path.
     *
     * What is stored is a key into the F1 secret store (`env`, encrypted
     * `filesystem`, or `vault` — an operator's choice, not the channel's). The
     * NAME is not sensitive, so it round-trips through reads normally. The VALUE
     * is resolved by {@see \CoolMS\CoreModule\Channel\ChannelConfigResolver}
     * immediately before `deliver()`, which keeps it out of the workflow's
     * persisted instance variables as well as out of `extras`.
     */
    public const string TYPE_SECRET_REF = 'secretRef';

    public function __construct(
        /** Key inside the channel's `$config` array — what `deliver()` reads. */
        public string $key,
        /** Human label for the admin input. */
        public string $label,
        /**
         * Input kind: `text`, `url`, `email`, `textarea`, `number`, or
         * {@see TYPE_SECRET_REF}.
         */
        public string $type = 'text',
        /**
         * A channel with a required field missing SKIPS rather than fails, so
         * this drives admin validation and nothing else. It is honest about
         * intent ("this channel cannot work without it") without turning a
         * half-configured section into a publish-time error.
         */
        public bool $required = false,
        /** One-line hint under the input. */
        public string $help = '',
        /** Placeholder text for the input. */
        public string $placeholder = '',
    ) {
    }

    /** True when this field holds a secret-store key rather than a plain value. */
    public function isSecretRef(): bool
    {
        return self::TYPE_SECRET_REF === $this->type;
    }

    /**
     * @return array<string, mixed> wire shape for the admin
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'required' => $this->required,
            'help' => $this->help,
            'placeholder' => $this->placeholder,
        ];
    }
}

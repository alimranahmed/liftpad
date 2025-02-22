<?php

namespace App\Supports\Cloudflare;

readonly class Tunnel
{
    public function __construct(
        public string $uuid,
        public string $name,
        public ?string $domain,
        public ?string $configFile,
    ) { }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'domain' => $this->domain,
            'configFile' => $this->configFile,
        ];
    }
}

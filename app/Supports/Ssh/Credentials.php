<?php

namespace App\Supports\Ssh;

readonly class Credentials
{
    public function __construct(
        public string $user,
        public string $host,
        public ?string $port,
        public ?string $password,
        public ?string $privateKeyPath,
    )
    {
    }

    public static function createFromArray(array $credentials): Credentials
    {
        return new static(
            user: $credentials['user'],
            host: $credentials['host'],
            port: $credentials['port'],
            password: $credentials['password'],
            privateKeyPath: $credentials['privateKeyPath'],
        );
    }
}

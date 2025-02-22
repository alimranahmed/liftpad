<?php

namespace App\Models;

use App\Supports\Ssh\Credentials;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property ?string $user
 * @property string $host
 * @property ?int $port
 * @property ?string $password
 * @property ?string $private_key_path
 */
class Server extends Model
{
    protected $casts = [
        'user' => 'encrypted',
        'host' => 'encrypted',
        'password' => 'encrypted',
        'private_key_path' => 'encrypted',
    ];

    public function toCredentials(): Credentials
    {
        return new Credentials(
            user: $this->user,
            host: $this->host,
            port: $this->port,
            password: $this->password,
            privateKeyPath: $this->private_key_path,
        );
    }
}

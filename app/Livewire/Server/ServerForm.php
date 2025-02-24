<?php

namespace App\Livewire\Server;

use App\Models\Server;
use App\Supports\Ssh\Ssh;
use Illuminate\Contracts\View\View;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ServerForm extends Component
{
    #[Validate('required|max:200')]
    public string $name = '';

    #[Validate('required|max:100')]
    public string $host = '';

    #[Validate('string|max:255|required')]
    public string $user = '';

    public ?string $sshPublicKey = null;

    public ?string $output = null;

    public ?string $createdServerUuid = null;

    public function saveServer(): void
    {
        $data = $this->validate();

        $data = [...$data, 'uuid' => Str::uuid7()];

        $server = Server::query()->create($data);

        $this->createdServerUuid = $server->uuid;

        $this->reset('name', 'host', 'user');

        $server = Server::query()->create($data);

        $output = null;
        $returnVar = null;

        Storage::createDirectory('.ssh');
        Storage::createDirectory('.ssh/liftpad_access');

        $sshPath = storage_path("app/private/.ssh/liftpad_access/{$server->uuid}");

        $command ="ssh-keygen -t rsa -b 4096 -C liftpad_server_{$server->uuid} -f {$sshPath} -N ''";
        exec($command, $output, $returnVar);
        $this->output = implode("\n", $output);

        if($returnVar === 0) {
            $file = new File("{$sshPath}.pub");
            $this->sshPublicKey = $file->getContent();
            $server->update(['private_key_path' => $sshPath]);
        }
    }

    public function checkConnection(string $serverUuid): string
    {
        $server = Server::query()->where('uuid', $serverUuid)->first();
        $process = Ssh::withCredentials($server->toCredentials())
            ->execute('whoami');

        $this->output = $process->getOutput();

        return $this->output;
    }

    public function render(): View
    {
        return view('livewire.server.server-form');
    }
}

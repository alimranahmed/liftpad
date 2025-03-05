<?php

namespace App\Livewire\Server;

use App\Livewire\CanStreamProcess;
use App\Models\Server;
use App\Supports\Ssh\Ssh;
use Illuminate\Contracts\View\View;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Create extends Component
{
    use CanStreamProcess;

    #[Validate('required|max:200')]
    public string $name = '';

    #[Validate('required|numeric|min:0')]
    public int $port = 22;

    #[Validate('required|max:100')]
    public string $host = '';

    #[Validate('string|max:255|required')]
    public string $user = '';

    public ?string $sshPublicKey = null;

    public string $streamTo = 'key-generation';

    public ?string $output = null;

    public ?string $createdServerUuid = null;

    public ?bool $isConnected = null;

    /**
     * @throws \Exception
     */
    public function saveServer(): void
    {
        try {
            $data = $this->validate();
        } catch (ValidationException $e) {
            $this->logAndStreamMessage('Validation ERROR: '.$e->getMessage());
            throw $e;
        }

        $data = [...$data, 'uuid' => Str::uuid7()];

        $server = Server::query()->create($data);

        $this->createdServerUuid = $server->uuid;

        $this->reset('name', 'host', 'user');

        $sshPath = $this->generateSshKey($server);

        if($sshPath !== null) {
            $file = new File("{$sshPath}.pub");
            $this->sshPublicKey = $file->getContent();
            $server->update(['private_key_path' => $sshPath]);
        }
    }

    /**
     * @throws \Exception
     */
    private function generateSshKey(Server $server): ?string
    {
        $this->logAndStreamMessage('Started generating SSH key');
        Storage::createDirectory('.ssh');
        Storage::createDirectory('.ssh/liftpad_access');

        $sshPath = storage_path("app/private/.ssh/liftpad_access/{$server->uuid}");

        $output = null;
        $returnVar = null;

        $command ="ssh-keygen -t rsa -b 4096 -C liftpad_server_{$server->uuid} -f {$sshPath} -N ''";
        exec($command, $output, $returnVar);

        $this->logAndStreamMessage(implode("\n", $output), $this->streamTo);

        return $returnVar === 0 ? $sshPath : null;
    }

    /**
     * @throws \Exception
     */
    public function checkConnection(string $serverUuid): void
    {
        $server = Server::query()->where('uuid', $serverUuid)->first();
        $process = Ssh::withCredentials($server->toCredentials())
            ->disableStrictHostKeyChecking()
            ->execute('whoami');

        $this->isConnected = $process->isSuccessful();

        $server->update([
            'is_connected' => $this->isConnected,
            'last_connection_checked_at' => now(),
        ]);
    }

    public function render(): View
    {
        return view('livewire.server.create');
    }
}

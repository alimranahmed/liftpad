<?php

namespace App\Livewire\Server;

use App\Models\Server;
use App\Supports\Ssh\Ssh;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url]
    public ?string $serverId = null;

    public Collection $servers;

    public ?bool $isConnected = null;

    public function mount(): void
    {
        $this->loadServers();
    }

    private function loadServers(): void
    {
        $this->servers = Server::query()->get();
    }

    public function deleteServer(string $serverUuid): void
    {
        Server::query()->where('uuid', $serverUuid)->delete();
        $this->loadServers();
    }

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

        $this->loadServers();
    }

    public function render(): View
    {
        return view('livewire.server.index');
    }
}

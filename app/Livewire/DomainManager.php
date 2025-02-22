<?php

namespace App\Livewire;

use App\Models\Server;
use App\Supports\Cloudflare\CloudFlareCli;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DomainManager extends Component
{
    use CanStreamProcess;

    public string $command = '';

    public string $output = '';

    public string $streamTo = 'display';

    public ?Server $server;

    public function mount(string $serverId): void
    {
        $this->server = Server::query()->where('uuid', $serverId)->firstOrFail();
    }

    /**
     * @throws \Exception
     */
    public function execute(): void
    {
        $process = $this->cloudflared()->ssh->executeAsync($this->command);

        $this->streamProcess($process, $this->streamTo);
    }

    public function cloudflared(): CloudFlareCli
    {
        return CloudFlareCli::create($this->server->toCredentials());
    }

    public function render(): View
    {
        return view('livewire.domain-manager');
    }
}

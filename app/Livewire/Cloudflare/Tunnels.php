<?php

namespace App\Livewire\Cloudflare;

use App\Livewire\CanStreamProcess;
use App\Models\Server;
use App\Supports\Cloudflare\CloudFlareCli;
use App\Supports\TerminalTheme\DraculaTheme;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

class Tunnels extends Component
{
    use CanStreamProcess;

    public array $tunnels;

    public string $serverId;

    public string $output = '';

    public string $streamTo = 'tunnel-screen';

    public Server $server;

    public function mount(string $serverId): void
    {
        $this->serverId = $serverId;
        $this->server = Server::query()->where('uuid', $this->serverId)->firstOrFail();
    }

    public function getTunnels(): void
    {
        $this->tunnels = $this->cloudflared()->getTunnels();
    }

    #[On('tunnelUpdated')]
    public function tunnelUpdated(): void
    {
        $this->getTunnels();
    }

    /**
     * @throws \Exception
     */
    public function deleteTunnel(string $tunnelUuid): void
    {
        $domain = $this->cloudflared()->getDomain($tunnelUuid);

        $this->logAndStreamMessage("Deletion started: domain: {$domain}, tunnel: {$tunnelUuid}");

        $this->logAndStreamMessage("Stopping service for: $domain");
        $output = $this->cloudflared()->changeServiceStatus($domain, 'stop');
        $this->logAndStreamMessage($output);
        $this->logAndStreamMessage("Stopped service for: $domain");

        $this->logAndStreamMessage("Deleting service file of: $domain");
        $output = $this->cloudflared()->deleteServiceFile($domain);
        $this->logAndStreamMessage($output);
        $this->logAndStreamMessage("Deleted service file of: $domain");

        $this->logAndStreamMessage("Deleting domain config for: $domain");
        $output = $this->cloudflared()->deleteTunnelConfig($domain);
        $this->logAndStreamMessage($output);
        $this->logAndStreamMessage("Deleted domain config for: $domain");

        $this->logAndStreamMessage("Deleting tunnel: $tunnelUuid");
        $output = $this->cloudflared()->deleteTunnel($tunnelUuid);
        $this->logAndStreamMessage($output);
        $this->logAndStreamMessage("Deleted tunnel: $tunnelUuid");

        $this->logAndStreamMessage("Deletion complete: domain: {$domain}, tunnel: {$tunnelUuid}");

        $this->getTunnels();
    }

    public function showTunnelConfig(string $configFile): void
    {
        $output = $this->cloudflared()->getTunnelConfig($configFile);

        $ansiConverter = new AnsiToHtmlConverter(new DraculaTheme());
        $this->output = $ansiConverter->convert($output);
    }

    public function cloudflared(): CloudFlareCli
    {
        return CloudFlareCli::create($this->server->toCredentials());
    }

    public function render(): View
    {
        return view('livewire.cloudflare.tunnels');
    }
}

<?php

namespace App\Livewire\Cloudflare;

use App\Models\Server;
use App\Supports\Cloudflare\CloudFlareCli;
use App\Supports\TerminalTheme\DraculaTheme;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

class Tunnels extends Component
{
    public array $tunnels;

    public string $serverId;

    public string $output = '';

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

    /**
     * @throws \Exception
     */
    public function deleteTunnel(string $tunnelUuid): void
    {
        $domain = $this->cloudflared()->getDomain($tunnelUuid);
        $this->output .= $this->cloudflared()->changeServiceStatus($domain, 'stop');
        $this->output .= $this->cloudflared()->deleteServiceFile($domain);
        $this->output .= $this->cloudflared()->deleteDnsRecord($tunnelUuid);
        $this->output .= $this->cloudflared()->deleteTunnelConfig($domain);
        $this->output .= $this->cloudflared()->deleteTunnel($tunnelUuid);

        $this->getTunnels();
    }

    public function showTunnelConfig(string $configFile): void
    {
        $output = $this->cloudflared()->getTunnelConfig($configFile);

        $ansiConverter = new AnsiToHtmlConverter(new DraculaTheme());
        $this->output = $ansiConverter->convert($output);
    }

    public function clearOutput(): void
    {
        $this->output = '';
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

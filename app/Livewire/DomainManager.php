<?php

namespace App\Livewire;

use App\Models\Server;
use App\Supports\Cloudflare\CloudFlareCli;
use App\Supports\TerminalTheme\DraculaTheme;
use Illuminate\Contracts\View\View;
use Illuminate\Process\InvokedProcess;
use Livewire\Component;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

class DomainManager extends Component
{
    use CanStreamProcess;

    public string $command = '';

    public string $output = '';

    public array $jobs = [
        'add_site' => 'Add the domain in Cloudflare',
        'change_nameservers' => 'Change nameserver to Cloudflare',
        'check_installed' => 'Is Cloudflared installed in server?',
        // cloudflared -v
        'is_cloudflare_tunnel_authorized' => 'Logged in tunnel using Cloudflared',
        // cloudflared tunnel login
        'check_service_installed' => 'Check if Cloudflared as service is installed or not',
        // cloudflared service install
        'domain' => [
            'create_tunnel' => 'Create a tunnel similar as domain name',
            // cloudflared tunnel create example-domain.com
            'configure_tunnel' => 'Configure tunnel to serve application on defined port',
            // create a file /root/.clouodflared/example-domain.com.yml
            'route_traffic' => 'Route request on Cloudflare via crated tunnel',
            // cloudflared tunnel route dns {tunnel-id} example-domain.com
            'add_service_entry' => 'Add the Cloudflared service entry for the domain',
            // Create service entry in /etc/systemd/system/example.com.service
            'start_domain_service' => 'Start the domain service',
            // systemctl start cloudflared.example-domain.com
            // systemctl restart cloudflared.example-domain.com
            // systemctl stop cloudflared.example-domain.com
        ],
    ];

    public ?Server $server;

    public function mount(string $serverId): void
    {
        $this->server = Server::query()->where('uuid', $serverId)->firstOrFail();
    }

    public function execute(): void
    {
        $process = CloudFlareCli::create($this->server->toCredentials())
            ->ssh->executeAsync($this->command);

        $this->streamProcess($process, 'display');
    }

    public function render(): View
    {
        return view('livewire.domain-manager');
    }
}

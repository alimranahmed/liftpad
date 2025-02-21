<?php

namespace App\Livewire;

use App\Supports\Ssh\Ssh;
use App\Supports\TerminalTheme\DraculaTheme;
use Illuminate\Contracts\View\View;
use Illuminate\Process\InvokedProcess;
use Livewire\Component;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

class CloudFlare extends Component
{

    public string $command = '';

    public string $output = '';

    public array $tunnelDomainPairs = [];

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

    public function mount(): void
    {
        //$this->findTunnels();
    }

    private function findTunnels(): void
    {
        $command = "cd /root/.cloudflared && cloudflared tunnel list";
        $process = Ssh::create('al_imran', '192.168.178.2')
            ->useSshPassPath('/opt/homebrew/bin/sshpass')
            ->usePassword('secret')
            ->usePrivateKey('/Users/al_imran/.ssh/id_rsa')
            ->executeAsync('echo secret | sudo -S bash -c "'.$command.'"');

        $outputString = '';
        while ($process->running()) {
            $outputString .= $process->latestOutput();
        }

        preg_match_all('/([a-f0-9\-]{36})\s+([\w\.-]+)/', $outputString, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $this->tunnelDomainPairs[] = ['tunnel_uuid' => $match[1], 'domain' => $match[2]];
        }
    }

    public function execute(): void
    {
        if ($this->command === '') {
            return;
        }
        $process = Ssh::create('al_imran', '192.168.178.2')
            ->useSshPassPath('/opt/homebrew/bin/sshpass')
            ->usePassword('secret')
            ->usePrivateKey('/Users/al_imran/.ssh/id_rsa')
            ->executeAsync('echo secret | sudo -S bash -c "'.$this->command.'"');

        $this->streamProcess($process);

    }

    private function streamProcess(InvokedProcess $process): void
    {
        $ansiConverter = new AnsiToHtmlConverter(new DraculaTheme());
        while ($process->running()) {
            $latest = $process->latestOutput();
            if ($latest !== '') {
                $this->output .= '<br>'.nl2br($ansiConverter->convert($latest));
                $this->stream(
                    to: 'display',
                    content: $this->output,
                    replace: true,
                );
            }
            $latest = $process->latestErrorOutput();
            if ($latest !== '') {
                $this->output .= '<br>'.nl2br($ansiConverter->convert($latest));
                $this->stream(
                    to: 'display',
                    content: $this->output,
                    replace: true,
                );
            }
        }
    }

    public function render(): View
    {
        return view('livewire.cloudflare');
    }
}

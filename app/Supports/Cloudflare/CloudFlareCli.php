<?php

namespace App\Supports\Cloudflare;

use App\Supports\Cloudflare\Exceptions\CloudflaredNotInstalled;
use App\Supports\Cloudflare\Exceptions\CommandFailed;
use App\Supports\Ssh\Credentials;
use App\Supports\Ssh\Ssh;
use Illuminate\Process\InvokedProcess;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class CloudFlareCli
{
    public function __construct(
        public readonly Credentials $credentials,
        public readonly Ssh $ssh,
    )
    {
    }

    public static function create(Credentials $credentials): self
    {
        $ssh = Ssh::withCredentials($credentials)
            ->disableStrictHostKeyChecking()
            ->setTimeout(30)
            ->sudo();

        return new static($credentials, $ssh);
    }

    public function logCommand(string $command, $output): void
    {
        Log::channel('command')->info($command."\n".$output);
    }

    /**
     * @throws CommandFailed
     */
    public function checkProcessFailure(Process $process): void
    {
        if (!$process->isSuccessful()) {
            throw new CommandFailed($process->getErrorOutput());
        }
    }

    /**
     * @throws \Exception
     */
    public function  getTunnels(): array
    {
        $domains = $this->getDomains();

        $command = "cd /root/.cloudflared && cloudflared tunnel list";
        $process = $this->ssh->executeAsync($command);

        $output = '';
        while ($process->running()) {
            $output .= $process->latestOutput();
            $output .= $process->latestErrorOutput();
        }

        preg_match_all('/([a-f0-9\-]{36})\s+([\w.-]+)/', $output, $matches, PREG_SET_ORDER);

        $tunnels = [];
        foreach ($matches as $match) {
            $tunnelUuid = $match[1];
            $tunnelName = $match[2];

            $domain = $domains[$tunnelUuid] ?? null;

            $tunnels[] = (new Tunnel(
                $tunnelUuid,
                $tunnelName,
                $domain['domain'] ?? '',
                $domain['configFile'] ?? null
            ))->toArray();
        }
        return $tunnels;
    }

    protected function getDomains(): array {
        $command = "ls /root/.cloudflared/";
        $output = $this->ssh->execute($command)->getOutput();
        preg_match_all('/([\w.-]+\.yml)/', $output, $tunnelConfigs);

        if (!isset($tunnelConfigs[0])) {
            return [];
        }

        $domains = [];
        foreach ($tunnelConfigs[0] as $tunnelConfig) {
            $command = "cat /root/.cloudflared/{$tunnelConfig}";
            $configContent =  $this->ssh->execute($command)->getOutput();

            preg_match('/tunnel:\s*([\w-]+)/', $configContent, $tunnelMatch);
            preg_match('/-\s*hostname:\s*([\w\.-]+)/', $configContent, $hostnameMatch);

            $tunnelId = $tunnelMatch[1] ?? null;
            $domain = $hostnameMatch[1] ?? null;
            $domains[$tunnelId] = ['domain' => $domain, 'configFile' => $tunnelConfig];
        }
        return $domains;
    }

    public function getTunnelConfig(string $configFile): string
    {
        $command = "cat /root/.cloudflared/{$configFile}";
        return $this->ssh->execute($command)->getOutput();
    }

    public function startCreatingTunnel(string $domain): InvokedProcess
    {
        $command = "cd /root/.cloudflared && cloudflared tunnel create {$domain}";
        return $this->ssh->executeAsync($command);
    }

    public function deleteTunnel(string $tunnelUuid): string
    {
        $allOutput = '';

        // Cleanup tunnel
        $command = "cd /root/.cloudflared && cloudflared tunnel cleanup {$tunnelUuid}";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        $allOutput .= $output;

        // Delete tunnel
        $command = "cd /root/.cloudflared && cloudflared tunnel delete {$tunnelUuid}";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        $allOutput .= $output;

        return $allOutput;
    }

    public function createDomainConfigFile(string $domain, string $content): string
    {
        $content = escapeshellarg($content);
        $fileName = "/root/.cloudflared/$domain.yml";
        $command = "echo $content > $fileName";
        $output = $this->ssh->execute($command)->getOutput();

        $this->logCommand($command, $output);

        return $output;
    }

    public function deleteTunnelConfig(?string $domain): string
    {
        if ($domain === null || $domain === '') {
            return "Cannot find tunnel domain config for unspecified domain";
        }
        $fileName = "/root/.cloudflared/$domain.yml";
        $command = "rm $fileName";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        return $output;
    }

    public function createDnsRecordViaTunnel(string $tunnelUuid, string $domain): string
    {
        $command = "cloudflared tunnel route dns $tunnelUuid $domain";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        return $output;
    }

    public function createAndStartTunnelRunnerService(string $domain, string $content): string
    {
        $content = escapeshellarg($content);
        $fileName = "/etc/systemd/system/cloudflared.{$domain}.service";
        $command = "echo $content > $fileName";
        $output = $this->ssh->execute($command)->getOutput();

        $this->logCommand($command, $output);

        $serviceOutput = $this->changeServiceStatus($domain, 'start');

        return "{$output}\n{$serviceOutput}";
    }

    public function deleteServiceFile(?string $domain): string
    {
        if ($domain === null || $domain === '') {
            return "Cannot find service file for unspecified domain";
        }
        $fileName = "/etc/systemd/system/cloudflared.{$domain}.service";
        $command = "rm $fileName";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        return $output;

    }

    /**
     * @throws \Exception
     */
    public function changeServiceStatus(string $domain, string $action): string
    {
        if (!in_array($action, ['start', 'stop', 'restart', 'status'])) {
            throw new \Exception('Invalid service action: '.$action);
        }

        $command = "systemctl {$action} cloudflared.{$domain}";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        return $output;
    }

    /**
     * @throws \Exception
     */
    public function getDomain(string $tunnelUuid): ?string
    {
        $domains = $this->getDomains();
        if (isset($domains[$tunnelUuid]['domain'])) {
            return $domains[$tunnelUuid]['domain'];
        }
        return $this->getTunnel($tunnelUuid)['name'] ?? null;
    }

    /**
     * @throws \Exception
     */
    public function getTunnel(string $tunnelUuid): ?array
    {
        return collect($this->getTunnels())
            ->where('uuid', $tunnelUuid)
            ->first();
    }

    public function updateCloudflared(): InvokedProcess
    {
        return $this->ssh->executeAsync("apt upgrade cloudflared -y");
    }

    public function latestVersion(): ?string
    {
        $response = Http::get('https://api.github.com/repos/cloudflare/cloudflared/releases/latest');
        if ($response->successful()) {
            return Arr::get($response->json(), 'tag_name');
        }
        return null;
    }

    /**
     * @throws CloudflaredNotInstalled
     */
    public function getVersion(): string
    {
        $process = $this->ssh
            ->execute("cloudflared --version | grep -oP '\d+\.\d+\.\d+'");

        if (!$process->isSuccessful()){
            $error = $process->getErrorOutput();
            if (preg_match('/(\w+): command not found/', $error, $matches)) {
                throw new CloudflaredNotInstalled($matches[0]);
            }
        }

        return $process->getOutput();
    }

    /**
     * @throws CommandFailed
     */
    public function installCloudflared(): ?InvokedProcess
    {
        // Add Cloudflare's package signing key:
        $process = $this->ssh->execute('mkdir -p --mode=0755 /usr/share/keyrings');
        $this->checkProcessFailure($process);

        $process = $this->ssh->execute('curl -fsSL https://pkg.cloudflare.com/cloudflare-main.gpg | sudo tee /usr/share/keyrings/cloudflare-main.gpg >/dev/null');
        $this->checkProcessFailure($process);

        // Add Cloudflare's apt repo to your apt repositories
        $process = $this->ssh->execute('sudo add-apt-repository ppa:certbot/certbot -y');
        $this->checkProcessFailure($process);

        $process = $this->ssh->execute('echo "deb [signed-by=/usr/share/keyrings/cloudflare-main.gpg] https://pkg.cloudflare.com/cloudflared any main" | sudo tee /etc/apt/sources.list.d/cloudflared.list');
        $this->checkProcessFailure($process);

        // Update repositories and install cloudflared
        return $this->ssh->executeAsync('sudo apt update && sudo apt install cloudflared');
    }

    /**
     * @throws CommandFailed
     */
    public function loginCloudflared(): InvokedProcess
    {
        return $this->ssh
            ->setTimeout(5*60)
            ->executeAsync('cloudflared tunnel login');
    }
}

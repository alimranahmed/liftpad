<?php

namespace App\Livewire;

use App\Models\Server;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class ServerManager extends Component
{
    /**
     * - Create uer:
     * sudo adduser liftpad_manager
     *
     * - Grant SSH Access
     * > ssh-keygen -t rsa -b 4096
     * > su - liftpad_manager
     * > mkdir -p ~/.ssh
     * > cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
     * > chmod 600 ~/.ssh/authorized_keys
     * > chmod 700 ~/.ssh
     * > exit
     *
     * - Grant sudo permission
     * > sudo usermod -aG sudo liftpad_manager
     * > sudo visudo
     * // Add the line below at the end of visudo
     * "liftpad_manager ALL=(ALL) NOPASSWD: ALL"
     *
     * - Configure ownership of deployment directory
     * > sudo chown -R liftpad_manager:www-data /var/www/html
     * > sudo chmod -R 755 /var/www/html
     *
     * - Enable SSH login for user
     * > sudo vim /etc/ssh/sshd_config
     * // Make sure these lines are added
     *
     * - Restart ssh
     * sudo systemctl restart ssh
     *
     * - Test ssh login
     * ssh -i ~/.ssh/id_rsa liftpad_manager@yourserver.com
     */

    #[Url]
    public ?string $serverId = null;

    public Collection $servers;

    public function mount(): void
    {
        $this->servers = Server::query()->get();
    }

    public function render(): View
    {
        return view('livewire.server-manager');
    }
}

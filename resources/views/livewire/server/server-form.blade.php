<div>
    <x-ui.drawer title="Add Server">
        <div>
            <pre class="whitespace-pre-line border rounded-xl bg-slate-800 text-slate-200 p-2 mb-3">
                sudo adduser liftpad_manager
                su - liftpad_manager
                mkdir -p ~/.ssh
                exit
                sudo usermod -aG sudo liftpad_manager
                sudo visudo
                // add the following line
                liftpad_manager ALL=(ALL) NOPASSWD:ALL
                sudo chown -R liftpad_manager:www-data /var/www/html
                sudo chmod -R 755 /var/www/html
                sudo systemctl restart ssh
            </pre>
        </div>
        <form wire:submit="saveServer">
            <div class="grid grid-cols-1 gap-y-3">
                <x-input type="text" wire:model="name" :shadowless="true" name="name" placeholder="Name"/>
                <x-input type="text" wire:model="host" :shadowless="true" name="host" icon="server" placeholder="Server IP/Hostname"/>
                <x-input type="text" wire:model="user" :shadowless="true" name="user" icon="user" placeholder="User name to login"/>
            </div>

            <div>
                <x-ui.button class="mt-3" wire:loading.remove wire:target="saveServer">Add</x-ui.button>
                <x-ui.button type="button" class="mt-3 cursor-not-allowed" wire:loading wire:target="saveServer">Adding...</x-ui.button>
            </div>
        </form>

        @if($sshPublicKey)
            <x-terminal.screen>{{$sshPublicKey}}</x-terminal.screen>

            <x-ui.button wire:click="checkConnection({{$createdServerUuid}})">Check Connection</x-ui.button>
        @endif

        @if($output)
            <x-terminal.screen>{{$output}}</x-terminal.screen>
        @endif
    </x-ui.drawer>
</div>

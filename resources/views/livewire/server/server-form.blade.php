<div x-data="{showTerminal: false}">
    <x-ui.drawer title="Add Server">
        <div>
            <span class="font-semibold">How to add a user in server</span>
            <span class="font-light text-gray-600">(eg. username: liftpad_manager)?</span>
        </div>
        <x-terminal.screen>
            <pre class="whitespace-pre-line">
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
        </x-terminal.screen>
        <form wire:submit="saveServer" class="my-3">
            <div class="grid grid-cols-1 gap-y-3">
                <x-input type="text" wire:model="name" :shadowless="true" name="name" placeholder="Name"/>
                <x-input type="text" wire:model="host" :shadowless="true" name="host" icon="server" placeholder="Server IP/Hostname"/>
                <x-input type="text" wire:model="user" :shadowless="true" name="user" icon="user" placeholder="User name to login"/>
            </div>

            <div>
                <x-ui.button class="mt-3" wire:loading.remove wire:target="saveServer" @click="showTerminal = true">Add</x-ui.button>
                <x-ui.button type="button" class="mt-3 cursor-not-allowed" wire:loading wire:target="saveServer">Adding...</x-ui.button>
            </div>
        </form>

        <x-terminal.screen stream="{{$streamTo}}">{!! $output !!}</x-terminal.screen>

        @if($sshPublicKey)
            <div class="mt-3 grid grid-cols-1 gap-3">
                <div class="font-semibold">Add this public key to the server</div>
                <x-terminal.screen>{{$sshPublicKey}}</x-terminal.screen>

                <x-ui.button wire:click="checkConnection('{{$createdServerUuid}}')">Check Connection</x-ui.button>

                <div>
                    @if($isConnected === true)
                        <span class="text-green-700">Connection established successfully!</span>
                    @elseif($isConnected === false)
                        <span class="text-red-700">Connection failed!</span>
                    @endif
                </div>
            </div>
        @endif
    </x-ui.drawer>
</div>

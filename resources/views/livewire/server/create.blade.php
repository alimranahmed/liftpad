<section>
    <div class="flex justify-between my-3">
        <div><h2 class="text-xl font-semibold">Add Server</h2></div>
        <div>
            <a href="{{route('server.index')}}" wire:navigate>
                <x-icon name="x-mark"></x-icon>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-3" x-data="{showTerminal: false}">
        <div class="col-span-12 md:col-span-7">
            <div>
                <span class="font-semibold"><span class="text-slate-400">#</span>&nbsp;How to add a user in server</span>
                <span class="font-light text-gray-600">(eg. username: <span class="italic">liftpad_manager</span>)?</span>
            </div>
            <ol class="list-decimal list-inside">
                <li>Add a user: <x-code>sudo adduser liftpad_manager</x-code></li>
                <li>Login as that user: <x-code>su - liftpad_manager</x-code></li>
                <li>Create ~/.ssh directory: <x-code>mkdir -p ~/.ssh && exit</x-code></li>
                <li>Give that user sudo permission: <x-code>sudo usermod -aG sudo liftpad_manager</x-code></li>
                <li>Allow no password login:
                    <ul class="list-inside pl-4">
                        <li>Open /etc/sudoers to edit: <x-code>sudo visudo</x-code></li>
                        <li>Add this line at the end: <x-code>liftpad_manager ALL=(ALL) NOPASSWD:ALL</x-code></li>
                    </ul>
                </li>
                <li>Restart ssh: <x-code>sudo systemctl restart ssh</x-code></li>
            </ol>
        </div>
        <div class="col-span-12 md:col-span-5">
            <div class="font-semibold">Login details</div>
            <form wire:submit="saveServer" class="my-3">
                <div class="grid grid-cols-1 gap-y-3">
                    <x-input type="text" wire:model="name" :shadowless="true" name="name" placeholder="Name(eg. Server 1)"/>
                    <div class="flex gap-3">
                        <x-input type="text" wire:model="host" :shadowless="true" name="host" icon="server" placeholder="Server IP/Hostname(eg. 192.168.172.2)" class="w-10/12"/>
                        <x-input type="number" wire:model="port" :shadowless="true" name="port" placeholder="Port"  class="w-2/12"/>
                    </div>
                    <x-input type="text" wire:model="user" :shadowless="true" name="user" icon="user" placeholder="User name to login(eg. liftpad_manager)"/>
                </div>

                <div>
                    <x-ui.button class="mt-3" wire:loading.remove wire:target="saveServer" @click="showTerminal = true">Add</x-ui.button>
                    <x-ui.button type="button" class="mt-3 cursor-not-allowed" wire:loading wire:target="saveServer">Adding...</x-ui.button>
                </div>
            </form>

            <x-terminal.screen stream="{{$streamTo}}">{!! $output !!}</x-terminal.screen>

            @if($sshPublicKey)
                <div class="mt-3 grid grid-cols-1 gap-3">
                    <div class="font-semibold">
                        Add this public key to the server<span class="text-slate-400">(ie. in /home/liftpad_manager/.ssh)</span>
                    </div>
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
        </div>
    </div>
</section>

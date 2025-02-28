<section x-data="{showServerForm: false}">
    <div class="flex justify-between my-3">
        <div><h2 class="text-md font-semibold">Servers</h2></div>
        <div>
            <x-ui.button class="rounded border h-full px-2" x-on:click="showServerForm = true">Add New Server</x-ui.button>
            <div x-show="showServerForm" x-transition @drawer-closed.window="showServerForm = false">
                <livewire:server.server-form/>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3">
        @forelse($servers as $server)
            <div class="relative border rounded-xl p-3">
                <div wire:click="deleteServer('{{$server->uuid}}')"
                    class="absolute rounded-full p-0.5 bg-slate-200 hover:bg-red-400 -top-1 -right-1 cursor-pointer">
                    <x-icon name="x-mark" class="h-3 w-3 text-slate-400 hover:text-red-800"/>
                </div>
                <div class="font-semibold">
                    {{$server->name}}
                    <div @class(['inline-block p-1.5 rounded-full', 'bg-green-600' => $server->is_connected, 'bg-red-600' => !$server->is_connected]) class=""> </div>
                </div>
                <div class="text-slate-500">{{$server->user.'@'.$server->host}}</div>
                <div>
                    <span class="cursor-pointer border px-1 rounded-md text-sm hover:text-gray-300"
                          wire:target="checkConnection"
                          wire:loading.class="cursor-wait"
                          wire:click="checkConnection('{{$server->uuid}}')">Connect</span>
                </div>
                <div class="col-span-3">
                    @if($isConnected === true)
                        <span class="text-green-700">Connection established successfully!</span>
                    @elseif($isConnected === false)
                        <span class="text-red-700">Connection failed!</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="mt-10 text-2xl text-gray-400 text-center col-span-3">No server added yet</div>
        @endforelse
    </div>

</section>

<section>
    <div class="flex justify-between my-3">
        <div><h2 class="text-xl font-semibold">Servers</h2></div>
        <div>
            <a href="{{route('server.create')}}" wire:navigate>
                <x-ui.button class="rounded border h-full px-2">Add New Server</x-ui.button>
            </a>
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
                    <span class="cursor-pointer border px-1 rounded-md text-sm text-gray-500 hover:text-gray-700"
                          wire:target="checkConnection('{{$server->uuid}}')"
                          wire:loading.class="cursor-wait"
                          wire:click="checkConnection('{{$server->uuid}}')">Check</span>
                </div>
                <div class="col-span-3">
                    @if(isset($connectionStatuses[$server->uuid]) && $connectionStatuses[$server->uuid] === true)
                        <span class="text-green-700">Connection established successfully!</span>
                    @elseif(isset($connectionStatuses[$server->uuid]) && $connectionStatuses[$server->uuid] === false)
                        <span class="text-red-700">Connection failed!</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="mt-10 text-2xl text-gray-400 text-center col-span-3">No server added yet</div>
        @endforelse
    </div>

</section>

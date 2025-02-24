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
        @foreach($servers as $server)
            <div class="border rounded-xl p-3">
                <div class="font-semibold">{{$server->name}}</div>
                <div class="text-slate-500">{{$server->user.'@'.$server->host}}</div>
            </div>
        @endforeach
    </div>

</section>

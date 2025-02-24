<section>
    <div class="flex justify-center my-3 gap-3" x-data="{showServerForm: false}">
        <div class="w-1/2 grid grid-cols-1">
            <select name="server_id" wire:model.live="serverId" id="server_id-id" name="server_id"
                    class="col-start-1 row-start-1 appearance-none rounded-md bg-white py-1.5 pl-3 pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                <option value="">Select Server</option>
                @foreach($servers as $server)
                    <option value="{{$server->uuid}}">{{$server->name}}</option>
                @endforeach
            </select>
            <svg class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>

    @if($serverId)
        <livewire:domain-manager serverId="{{$serverId}}"/>
    @endif

</section>

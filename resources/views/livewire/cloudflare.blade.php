<section>
    <form class="mt-5 flex border dark:border-gray-800 rounded-t-xl p-1 justify-between bg-white" wire:submit="execute">
        <input type="text"
               autofocus="autofocus"
               class="py-2 px-1 w-full text-center focus:outline-none border-xl text-gray-800"
               name="command"
               wire:model="command"
               placeholder="Command..."
               aria-label="Command">
        <div class="flex items-center px-2">
            <button type="submit" class="text-green-600" wire:loading.remove>Run</button>
            <span class="text-indigo-600 cursor-not-allowed" disabled wire:loading>Running...</span>
        </div>
    </form>
    <div class="bg-gray-800 text-white p-6 rounded-b-xl overflow-hidden font-mono text-sm whitespace-pre-wrap h-80 overflow-y-scroll"
         wire:stream="display" >{!! $output !!}</div>


    <div>
        @foreach($tunnelDomainPairs as $tunnelDomainPair)
            <div>Tunnel: {{$tunnelDomainPair['tunnel_uuid']}}</div>
            <div>Domain: {{$tunnelDomainPair['domain']}}</div>
        @endforeach
    </div>

</section>

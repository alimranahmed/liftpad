<div class="border rounded-xl grid grid-cols-1 gap-3 p-3" x-data="{ showTerminal: false }">
    <form wire:submit="addDomain">
        <div class="flex gap-3 py-1 text-gray-900">
            <input name="domain"
                   placeholder="Domain name(eg. example.com)"
                   wire:model="domain"
                   class="border focus:outline-none rounded-md w-full py-1 px-2">

            <input name="domain"
                   placeholder="Port"
                   type="number"
                   wire:model="port"
                   class="border focus:outline-none rounded-md py-1 px-2">

            <x-ui.button class="border rounded-md px-2 dark:text-white"
                    @click="showTerminal = true"
            >Add</x-ui.button>
        </div>

        @if($errors->any())
            <div class="text-red-600">
                @foreach($errors->all() as $error)
                    {{$error}}
                @endforeach
            </div>
        @endif
    </form>

    <x-terminal.screen stream="{{$streamTo}}">{!! $output !!}</x-terminal.screen>
</div>

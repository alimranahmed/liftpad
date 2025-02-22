<div>
    <div x-data="{ showTerminal: false }">
        <form wire:submit="addDomain" class="my-3">
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

                <button class="border rounded-md px-2 dark:text-white"
                        @click="showTerminal = true"
                >Add</button>
            </div>

            @if($errors->any())
                <div class="text-red-600">
                    @foreach($errors->all() as $error)
                        {{$error}}
                    @endforeach
                </div>
            @endif
        </form>

        <div x-show="showTerminal" x-transition>
            <x-terminal.screen stream="{{$streamTo}}">{!! $output !!}</x-terminal.screen>
        </div>
    </div>
</div>

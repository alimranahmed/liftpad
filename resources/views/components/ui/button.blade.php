@props(['secondary' => false, 'type' => 'submit'])

@php
if ($secondary) {
    $class = "rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50";
} else {
    $class = "rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600";
}
@endphp

<button type="{{$type}}" {{$attributes->merge(['class' => $class])}}>{{$slot}}</button>

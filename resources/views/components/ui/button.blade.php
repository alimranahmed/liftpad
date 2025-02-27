@props(['secondary' => false, 'type' => 'submit', 'size' => 'md'])

@php
$sizeClass =  [
    'md' => 'px-3 py-2 text-sm',
    'sm' => 'px-2 py-1 text-xs',
][$size];

if ($secondary) {
    $class = "rounded-md bg-white {$sizeClass} font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50";
} else {
    $class = "rounded-md bg-indigo-600 {$sizeClass} font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600";
}
@endphp

<button type="{{$type}}" {{$attributes->merge(['class' => $class])}}>{{$slot}}</button>

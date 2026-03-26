@props(['header' => null])

@include('layouts.app-sidebar', ['header' => $header, 'slot' => $slot])

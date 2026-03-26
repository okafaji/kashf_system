@props(['align' => 'between'])

<div {{ $attributes->merge(['class' => 'flex items-center justify-' . $align . ' gap-3']) }}>
    {{ $slot }}
</div>

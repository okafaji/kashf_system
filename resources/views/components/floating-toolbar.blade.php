<div class="floating-toolbar-container" style="display: flex; justify-content: center; margin-top: 40px; margin-bottom: 24px; position: sticky; top: 40px; z-index: 1050;">
    <div class="floating-toolbar max-w-3xl w-full bg-white border border-gray-200 rounded-lg shadow-sm p-4 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight whitespace-nowrap shrink-0 mb-0">
                {{ $title ?? '' }}
            </h2>
            @if(isset($subtitle))
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-bold">{{ $subtitle }}</span>
            @endif
        </div>
        <div class="flex gap-2 flex-wrap">
            {{ $slot }}
        </div>
    </div>
</div>
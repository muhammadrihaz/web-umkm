@props(['compact' => false, 'light' => false])
<span {{ $attributes->merge(['class' => 'brand-lockup'.($light ? ' brand-lockup--light' : '').($compact ? ' brand-lockup--compact' : '')]) }}>
    <svg class="brand-mark" viewBox="0 0 56 48" aria-hidden="true">
        <path d="M7 38h42v4H7z" fill="currentColor" opacity=".22"/>
        <path d="M12 34h32v5H12zM16 26h24v8H16zM20 18h16v8H20z" fill="currentColor"/>
        <path d="M8 26h40L28 7z" fill="#d9982e"/>
        <path d="M15 25h26L28 13z" fill="#f3cb71"/>
        <path d="M25 26h6v13h-6z" fill="#fff8ed"/>
        <path d="M5 42c9 3 15 3 23 0 8 3 14 3 23 0" fill="none" stroke="#4c8b3a" stroke-width="3" stroke-linecap="round"/>
    </svg>
    <span class="brand-copy">
        <strong>Pangkonan</strong>
        @unless($compact)<small>Oleh-Oleh Khas Gianyar</small>@endunless
    </span>
</span>

@props(['name', 'size' => 20])
<svg {{ $attributes->merge(['class' => 'ui-icon', 'width' => $size, 'height' => $size, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.9', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
    @switch($name)
        @case('home')<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10M9 20v-6h6v6"/>@break
        @case('search')<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>@break
        @case('cart')<path d="M3 4h2l2 11h10l2-8H6"/><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/>@break
        @case('user')<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>@break
        @case('grid')<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>@break
        @case('orders')<path d="M6 3h12v18H6z"/><path d="M9 7h6M9 11h6M9 15h4"/>@break
        @case('product')<path d="m4 7 8-4 8 4-8 4z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4z"/><path d="M12 11v10"/>@break
        @case('stock')<path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/>@break
        @case('users')<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M16 5a3 3 0 0 1 0 6M17 14a5 5 0 0 1 4 5"/>@break
        @case('chart')<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>@break
        @case('payment')<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/>@break
        @case('settings')<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-1.6v-.2h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1Z"/>@break
        @case('truck')<path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>@break
        @case('pickup')<path d="M4 10h16l-2-6H6zM5 10v10h14V10M9 20v-6h6v6"/><path d="M3 10h18"/>@break
        @case('shield')<path d="M12 3 4 6v6c0 5 3.4 8 8 9 4.6-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>@break
        @case('headset')<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><path d="M4 14h3v6H5a1 1 0 0 1-1-1zM20 14h-3v6h2a1 1 0 0 0 1-1zM17 20c0 1-2 1-3 1"/>@break
        @case('gift')<path d="M3 9h18v12H3zM12 9v12M2 5h20v4H2z"/><path d="M12 5H8.5a2 2 0 1 1 2-2c0 1.3 1.5 2 1.5 2Zm0 0h3.5a2 2 0 1 0-2-2C13.5 4.3 12 5 12 5Z"/>@break
        @case('location')<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>@break
        @case('plus')<path d="M12 5v14M5 12h14"/>@break
        @case('arrow')<path d="M5 12h14M14 7l5 5-5 5"/>@break
        @case('whatsapp')<path d="M20 11.5a8 8 0 0 1-11.8 7L4 20l1.4-4A8 8 0 1 1 20 11.5Z"/><path d="M9 8.5c.4 2.5 2 4.2 4.6 5l1-1.2 1.8.8c-.2 1.4-1.1 2.1-2.6 2.1-3.6-.4-6.4-3.1-6.8-6.7 0-1 .5-1.7 1.5-2l.5 2Z"/>@break
        @case('bell')<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>@break
        @case('logout')<path d="M10 17l5-5-5-5M15 12H3M14 3h7v18h-7"/>@break
        @case('lock')<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>@break
        @case('trash')<path d="M4 7h16M9 7V4h6v3M7 7l1 14h8l1-14M10 11v6M14 11v6"/>@break
        @default<circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/>
    @endswitch
</svg>

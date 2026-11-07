@props(['compact' => false, 'light' => false])
<span {{ $attributes->merge(['class' => 'brand-lockup'.($light ? ' brand-lockup--light' : '').($compact ? ' brand-lockup--compact' : '')]) }}>
    <img class="brand-logo" src="{{ asset('assets/logo.png') }}" alt="Pangkonan" width="1191" height="794">
</span>

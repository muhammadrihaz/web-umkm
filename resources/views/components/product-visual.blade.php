@props(['product', 'class' => ''])
@php($visualVariant = (($product->id ?? 0) % 4) + 1)
<div class="product-visual product-visual--{{ $visualVariant }} {{ $class }}">
    @if($product->images->first())
        <img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="{{ $product->images->first()->alt_text ?: $product->name }}">
    @else
        <svg viewBox="0 0 640 420" role="img" aria-label="Ilustrasi {{ $product->name }}">
            <rect width="640" height="420" fill="var(--visual-sky)"/>
            <circle cx="545" cy="76" r="54" fill="#f4c65e" opacity=".65"/>
            <path d="M0 310c108-62 200-12 296-48 128-48 222-28 344 32v126H0z" fill="#71964b"/>
            <path d="M0 341c115-40 214-8 327-35 126-30 215-7 313 35v79H0z" fill="#496f37"/>
            <path d="M55 260h105l-52-126z" fill="#735137" opacity=".92"/>
            <path d="M73 231h70l-35-86z" fill="#c98b3b"/>
            @if($visualVariant === 1)
                <g transform="translate(184 100)">
                    <ellipse cx="168" cy="245" rx="150" ry="40" fill="#8a542c" opacity=".45"/>
                    @foreach([[45,170,62],[118,126,69],[210,153,66],[286,118,56],[267,213,57],[150,220,63],[65,237,53]] as [$x,$y,$r])
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $r }}" fill="#e8a744" stroke="#c77b24" stroke-width="8"/>
                        <path d="M{{ $x-28 }} {{ $y-7 }}q28-26 56 0" fill="none" stroke="#f5cf7a" stroke-width="8" stroke-linecap="round"/>
                    @endforeach
                </g>
            @elseif($visualVariant === 2)
                <g transform="translate(220 110)">
                    <ellipse cx="115" cy="245" rx="145" ry="37" fill="#6d3e26" opacity=".45"/>
                    @foreach([[30,175],[75,105],[120,175],[165,95],[205,165],[255,115],[280,200],[105,230],[205,235]] as [$x,$y])
                        <path d="M{{ $x }} {{ $y }}c18-28 52-20 56 8-5 34-48 42-62 12-5-10-2-20 6-20Z" fill="#c47728" stroke="#8c4b18" stroke-width="7"/>
                        <path d="m{{ $x+10 }} {{ $y+8 }} 34 17" stroke="#efc162" stroke-width="5"/>
                    @endforeach
                </g>
            @elseif($visualVariant === 3)
                <g transform="translate(180 98)">
                    <ellipse cx="150" cy="258" rx="160" ry="35" fill="#5f3c24" opacity=".38"/>
                    @foreach([[20,150,20],[95,95,-14],[185,125,8],[275,80,-20],[260,180,22],[150,210,-5],[45,230,12]] as [$x,$y,$r])
                        <g transform="translate({{ $x }} {{ $y }}) rotate({{ $r }})"><path d="M0 0c38-20 82-4 103 25-34 35-75 42-111 8Z" fill="#f1c64f" stroke="#d69b25" stroke-width="7"/><path d="M15 17c28-8 51-5 72 8" fill="none" stroke="#fff2a6" stroke-width="6"/></g>
                    @endforeach
                </g>
            @else
                <g transform="translate(205 62)">
                    <path d="M44 82h210l-18 230H62z" fill="#f8e4bd" stroke="#b87535" stroke-width="8"/>
                    <path d="M44 82 85 32h128l41 50" fill="#d74939"/>
                    <path d="M92 32c30 35 78 35 112 0" fill="none" stroke="#7b1e28" stroke-width="12"/>
                    <path d="M88 117h122v108H88z" fill="#fff8e9" stroke="#d8b46a" stroke-width="5"/>
                    <circle cx="149" cy="167" r="32" fill="#efb640"/><path d="M128 176c15-35 36-35 47 0" fill="none" stroke="#8d4e25" stroke-width="8"/>
                    <path d="m20 82 129 96L278 82" fill="none" stroke="#b72d2b" stroke-width="10" opacity=".82"/>
                </g>
            @endif
            <g fill="#fff4c7"><circle cx="65" cy="332" r="10"/><circle cx="88" cy="352" r="6"/><circle cx="575" cy="325" r="9"/></g>
        </svg>
    @endif
</div>

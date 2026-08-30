@props(['name'])

<svg {{ $attributes->merge(['class' => 'product-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('sales') <circle cx="9" cy="20" r="1"/><circle cx="19" cy="20" r="1"/><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L22 8H6"/> @break
        @case('cash') <rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 10h.01M18 14h.01"/> @break
        @case('expenses') <path d="M12 2v20M17 7.5C17 5.6 15 4 12.5 4S8 5.3 8 7s1.5 2.5 4.5 3 4.5 1.4 4.5 3.5-2 3.5-4.5 3.5S8 15.4 8 13.5"/><path d="m19 5 3 3m0-3v3h-3"/> @break
        @case('products') <path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 17l9 5 9-5"/> @break
        @case('stock') <path d="M4 7h16v13H4zM7 4h10v3M8 11h8M8 15h5"/> @break
        @case('purchases') <path d="M6 2v4M18 2v4M3 9h18M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z"/><path d="m8 14 2 2 5-5"/> @break
        @case('central') <rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 8h8M8 12h5M8 16h7"/> @break
        @case('simple') <path d="m4 12 5 5L20 6"/><circle cx="12" cy="12" r="10"/> @break
        @case('insights') <path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/> @break
        @case('store') <path d="M3 9h18l-2-5H5L3 9Z"/><path d="M5 9v11h14V9M9 20v-6h6v6"/> @break
        @case('food') <path d="M4 3v7a3 3 0 0 0 3 3V3M7 13v8M14 3v18M14 3c4 2 5 6 5 9h-5"/> @break
        @case('truck') <path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/> @break
        @case('shop') <path d="M4 10v10h16V10M3 10l2-6h14l2 6"/><path d="M8 20v-6h8v6"/> @break
        @case('inventory') <path d="m12 3 9 4.5-9 4.5-9-4.5L12 3Z"/><path d="M3 7.5V17l9 4 9-4V7.5M12 12v9"/> @break
    @endswitch
</svg>

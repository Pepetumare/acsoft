@props([
    'label' => 'Hablar por WhatsApp',
    'class' => 'btn btn-whatsapp',
    'message' => null,
])

@php
    $number = config('acsoft.whatsapp');

    $message = $message ?? config('acsoft.whatsapp_message');

    $whatsappUrl = $number
        ? 'https://wa.me/' . $number . '?text=' . urlencode($message)
        : '#';
@endphp

<a
    href="{{ $whatsappUrl }}"
    class="{{ $class }}"
    target="_blank"
    rel="noopener noreferrer"
>
    {{ $label }}
</a>
@props(['model'])

<span {{ $attributes->merge(['class' => 'badge '.$model->statusBadgeClass()]) }}>
    {{ $model->statusLabel() }}
</span>

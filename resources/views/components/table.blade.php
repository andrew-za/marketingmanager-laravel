@props([
    'striped' => false,
    'hover' => true,
])

@php
    $tableClasses = 'min-w-full divide-y divide-gray-200';
    $theadClasses = 'bg-gray-50';
    $tbodyClasses = 'bg-white divide-y divide-gray-200';
    
    if ($striped) {
        $tbodyClasses .= ' [&>tr:nth-child(even)]:bg-gray-50';
    }
    
    if ($hover) {
        $tbodyClasses .= ' [&>tr:hover]:bg-gray-50';
    }
@endphp

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => $tableClasses]) }}>
        @if(isset($thead))
            <thead class="{{ $theadClasses }}">
                {{ $thead }}
            </thead>
        @endif
        
        @if(isset($tbody))
            <tbody class="{{ $tbodyClasses }}">
                {{ $tbody }}
            </tbody>
        @endif
        
        @if(isset($tfoot))
            <tfoot class="{{ $theadClasses }}">
                {{ $tfoot }}
            </tfoot>
        @endif
    </table>
</div>


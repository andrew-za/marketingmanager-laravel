@extends('layouts.app')

@section('page-title', 'Content Calendar')

@section('content')
<div id="content-calendar-app" data-organization-id="{{ $organizationId }}">
    <content-calendar-component :organization-id="{{ $organizationId }}"></content-calendar-component>
</div>
@endsection

@push('scripts')
<script type="module">
import { createApp } from 'vue';
import ContentCalendarComponent from '/resources/js/components/ContentCalendarComponent.vue';

const app = createApp({});
app.component('content-calendar-component', ContentCalendarComponent);
app.mount('#content-calendar-app');
</script>
@endpush



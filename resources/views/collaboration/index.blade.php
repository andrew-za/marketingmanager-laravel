@extends('layouts.app')

@section('page-title', 'Collaboration')

@section('content')
<div id="collaboration-app" data-organization-id="{{ $organizationId }}">
    <collaboration-component :organization-id="'{{ $organizationId }}'"></collaboration-component>
</div>
@endsection

@push('scripts')
<script type="module">
import { createApp } from 'vue';
import CollaborationComponent from '/resources/js/components/CollaborationComponent.vue';

const app = createApp({});
app.component('collaboration-component', CollaborationComponent);
app.mount('#collaboration-app');
</script>
@endpush


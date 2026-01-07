@extends('layouts.app')

@section('page-title', 'Content Review')

@section('content')
<div id="review-app" data-organization-id="{{ $organizationId }}">
    <review-component :organization-id="'{{ $organizationId }}'"></review-component>
</div>
@endsection

@push('scripts')
<script type="module">
import { createApp } from 'vue';
import ReviewComponent from '/resources/js/components/ReviewComponent.vue';

const app = createApp({});
app.component('review-component', ReviewComponent);
app.mount('#review-app');
</script>
@endpush


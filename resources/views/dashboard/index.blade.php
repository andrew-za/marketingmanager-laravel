@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div id="dashboard-app" data-organization-id="{{ $organizationId }}">
    <dashboard-component></dashboard-component>
</div>
@endsection

@push('scripts')
<script type="module">
import { createApp } from 'vue';
import DashboardComponent from '/resources/js/components/DashboardComponent.vue';

const app = createApp({});
app.component('dashboard-component', DashboardComponent);
app.mount('#dashboard-app');
</script>
@endpush



@extends('layouts.app')

@section('page-title', $campaign->name ?? 'Campaign Details')

@section('content')
<div id="campaign-timeline-app" 
     data-campaign-id="{{ $campaign->id }}" 
     data-organization-id="{{ $organizationId }}">
    <campaign-timeline-component 
        :campaign-id="{{ $campaign->id }}"
        :organization-id="{{ $organizationId }}"
    ></campaign-timeline-component>
</div>
@endsection

@push('scripts')
<script type="module">
import { createApp } from 'vue';
import CampaignTimelineComponent from '/resources/js/components/CampaignTimelineComponent.vue';

const app = createApp({});
app.component('campaign-timeline-component', CampaignTimelineComponent);
app.mount('#campaign-timeline-app');
</script>
@endpush



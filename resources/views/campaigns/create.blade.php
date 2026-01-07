@extends('layouts.app')

@section('page-title', 'Create Campaign')

@section('content')
<div id="campaign-creation-app" 
     data-organization-id="{{ $organizationId }}"
     data-brand-id="{{ $brandId }}">
    <campaign-creation-wizard
        :organization-id="{{ $organizationId }}"
        :brand-id="{{ $brandId ?: 'null' }}"
        :brands="{{ json_encode($brands) }}"
        :products="{{ json_encode($products) }}"
        :channels="{{ json_encode($channels) }}"
    ></campaign-creation-wizard>
</div>
@endsection

@push('scripts')
<script type="module">
import { createApp } from 'vue';
import CampaignCreationWizard from '/resources/js/components/CampaignCreationWizard.vue';

const app = createApp({});
app.component('campaign-creation-wizard', CampaignCreationWizard);
app.mount('#campaign-creation-app');
</script>
@endpush


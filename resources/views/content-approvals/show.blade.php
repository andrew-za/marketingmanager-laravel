@extends('layouts.app')

@section('page-title', 'Content Approval')

@section('content')
<div id="content-approval-app" data-approval-id="{{ $approval->id }}" data-organization-id="{{ $organizationId }}">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-gray-900">Content Approval</h2>
            <p class="text-gray-600 mt-1">Review and approve scheduled content</p>
        </div>

        <div class="card mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold">{{ $approval->scheduledPost->campaign->name ?? 'Scheduled Post' }}</h3>
                    <p class="text-sm text-gray-500">Channel: {{ $approval->scheduledPost->channel->display_name ?? 'N/A' }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $approval->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($approval->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                    {{ ucfirst($approval->status) }}
                </span>
            </div>

            <div class="mb-6">
                <h4 class="font-semibold mb-2">Content</h4>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="whitespace-pre-wrap">{{ $approval->scheduledPost->content }}</p>
                </div>
            </div>

            <div class="mb-6">
                <h4 class="font-semibold mb-2">Scheduled Date & Time</h4>
                <p class="text-gray-700">{{ $approval->scheduledPost->scheduled_at->format('F j, Y g:i A') }}</p>
            </div>

            @if($approval->comments)
            <div class="mb-6">
                <h4 class="font-semibold mb-2">Comments</h4>
                <p class="text-gray-700">{{ $approval->comments }}</p>
            </div>
            @endif

            <!-- Platform Preview -->
            <div class="mb-6">
                <h4 class="font-semibold mb-4">Platform Preview</h4>
                <platform-content-preview-component 
                    :content="{{ json_encode($approval->scheduledPost->content) }}"
                    :platforms="[{{ json_encode($approval->scheduledPost->channel->platform ?? 'facebook') }}]"
                ></platform-content-preview-component>
            </div>

            @if($approval->status === 'pending' && auth()->id() === $approval->approver_id)
            <div class="flex items-center space-x-4">
                <form method="POST" action="{{ route('main.content-approvals.approve', ['organizationId' => $organizationId, 'approval' => $approval->id]) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">Approve</button>
                </form>
                <button @click="showRejectModal = true" class="btn btn-danger">Reject</button>
            </div>
            @endif
        </div>

        <!-- Approval History -->
        <div class="card">
            <h3 class="text-lg font-semibold mb-4">Approval History</h3>
            <div class="space-y-3">
                @foreach($approvalHistory as $history)
                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="text-primary-600 text-xs font-semibold">
                            {{ $history->approver->name[0] }}
                        </span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">{{ $history->approver->name }}</span>
                            <span class="text-xs text-gray-500">{{ $history->created_at->diffForHumans() }}</span>
                        </div>
                        <span class="text-sm {{ $history->status === 'approved' ? 'text-green-600' : 'text-red-600' }}">
                            {{ ucfirst($history->status) }}
                        </span>
                        @if($history->comments)
                        <p class="text-sm text-gray-600 mt-1">{{ $history->comments }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
import { createApp } from 'vue';
import PlatformContentPreviewComponent from '/resources/js/components/PlatformContentPreviewComponent.vue';

const app = createApp({});
app.component('platform-content-preview-component', PlatformContentPreviewComponent);
app.mount('#content-approval-app');
</script>
@endpush



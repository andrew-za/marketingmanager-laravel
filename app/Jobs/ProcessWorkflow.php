<?php

namespace App\Jobs;

use App\Models\Workflow;
use App\Services\Workflow\WorkflowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Process Workflow Job
 * Executes workflow asynchronously
 */
class ProcessWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Workflow $workflow,
        public array $inputData = []
    ) {}

    public function handle(WorkflowService $workflowService): void
    {
        try {
            $workflowService->executeWorkflow($this->workflow, $this->inputData);
            Log::info("Workflow {$this->workflow->id} executed successfully");
        } catch (\Exception $e) {
            Log::error("Failed to execute workflow {$this->workflow->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessWorkflow job failed after {$this->tries} attempts for workflow {$this->workflow->id}: " . $exception->getMessage());
    }
}


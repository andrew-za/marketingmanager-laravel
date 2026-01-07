<?php

namespace App\Services\Workflow;

use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Models\WorkflowActionTemplate;
use App\Models\AutomationRule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WorkflowService
{
    public function createWorkflow(array $data, User $user): Workflow
    {
        return DB::transaction(function () use ($data, $user) {
            return Workflow::create([
                ...$data,
                'organization_id' => $user->primaryOrganization()->id,
                'created_by' => $user->id,
            ]);
        });
    }

    public function updateWorkflow(Workflow $workflow, array $data): Workflow
    {
        return DB::transaction(function () use ($workflow, $data) {
            $workflow->update($data);
            return $workflow->fresh();
        });
    }

    public function deleteWorkflow(Workflow $workflow): bool
    {
        return DB::transaction(function () use ($workflow) {
            return $workflow->delete();
        });
    }

    public function executeWorkflow(Workflow $workflow, array $inputData = []): WorkflowExecution
    {
        return DB::transaction(function () use ($workflow, $inputData) {
            $execution = WorkflowExecution::create([
                'workflow_id' => $workflow->id,
                'status' => 'running',
                'input_data' => $inputData,
                'started_at' => now(),
            ]);

            try {
                $outputData = $this->processWorkflowSteps($workflow, $inputData);
                
                $execution->update([
                    'status' => 'completed',
                    'output_data' => $outputData,
                    'completed_at' => now(),
                    'duration_seconds' => now()->diffInSeconds($execution->started_at),
                ]);
            } catch (\Exception $e) {
                $execution->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                    'duration_seconds' => now()->diffInSeconds($execution->started_at),
                ]);
            }

            return $execution->fresh();
        });
    }

    private function processWorkflowSteps(Workflow $workflow, array $inputData): array
    {
        $steps = $workflow->steps ?? [];
        $outputData = [];

        foreach ($steps as $step) {
            $actionType = $step['action_type'] ?? null;
            $config = $step['config'] ?? [];

            switch ($actionType) {
                case 'send_email':
                    $outputData[] = $this->executeEmailAction($config, $inputData);
                    break;
                case 'create_task':
                    $outputData[] = $this->executeTaskAction($config, $inputData);
                    break;
                case 'update_status':
                    $outputData[] = $this->executeStatusAction($config, $inputData);
                    break;
                default:
                    $outputData[] = ['action' => $actionType, 'status' => 'skipped'];
            }
        }

        return $outputData;
    }

    private function executeEmailAction(array $config, array $inputData): array
    {
        return ['action' => 'send_email', 'status' => 'completed'];
    }

    private function executeTaskAction(array $config, array $inputData): array
    {
        return ['action' => 'create_task', 'status' => 'completed'];
    }

    private function executeStatusAction(array $config, array $inputData): array
    {
        return ['action' => 'update_status', 'status' => 'completed'];
    }

    public function createActionTemplate(array $data): WorkflowActionTemplate
    {
        return WorkflowActionTemplate::create($data);
    }

    public function testWorkflow(Workflow $workflow, array $inputData = []): array
    {
        try {
            $outputData = $this->processWorkflowSteps($workflow, $inputData);
            return [
                'success' => true,
                'output' => $outputData,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createAutomationRule(array $data, User $user): AutomationRule
    {
        return AutomationRule::create([
            'organization_id' => $user->primaryOrganization()->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'trigger_conditions' => $data['trigger_conditions'],
            'actions' => $data['actions'],
            'is_active' => $data['is_active'] ?? true,
            'priority' => $data['priority'] ?? 0,
            'created_by' => $user->id,
        ]);
    }

    public function evaluateAutomationRule(AutomationRule $rule, array $context): bool
    {
        $conditions = $rule->trigger_conditions;
        
        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? 'equals';
            $value = $condition['value'] ?? null;
            
            if (!$this->evaluateCondition($field, $operator, $value, $context)) {
                return false;
            }
        }
        
        return true;
    }

    private function evaluateCondition(?string $field, string $operator, $value, array $context): bool
    {
        $fieldValue = $context[$field] ?? null;
        
        return match ($operator) {
            'equals' => $fieldValue === $value,
            'not_equals' => $fieldValue !== $value,
            'contains' => str_contains($fieldValue ?? '', $value),
            'greater_than' => $fieldValue > $value,
            'less_than' => $fieldValue < $value,
            default => false,
        };
    }
}


<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskService
{
    public function createTask(array $data, User $user): Task
    {
        return DB::transaction(function () use ($data, $user) {
            return Task::create([
                'organization_id' => $user->primaryOrganization()->id,
                'project_id' => $data['project_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'todo',
                'priority' => $data['priority'] ?? 'medium',
                'assignee_id' => $data['assignee_id'] ?? null,
                'created_by' => $user->id,
                'due_date' => isset($data['due_date']) ? new \DateTime($data['due_date']) : null,
            ]);
        });
    }

    public function updateTask(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            if (isset($data['due_date'])) {
                $data['due_date'] = new \DateTime($data['due_date']);
            }

            $task->update($data);
            return $task->fresh();
        });
    }

    public function deleteTask(Task $task): bool
    {
        return DB::transaction(function () use ($task) {
            return $task->delete();
        });
    }

    public function assignTask(Task $task, ?int $assigneeId): Task
    {
        return DB::transaction(function () use ($task, $assigneeId) {
            $task->update(['assignee_id' => $assigneeId]);
            return $task->fresh();
        });
    }

    public function updateTaskStatus(Task $task, string $status): Task
    {
        return DB::transaction(function () use ($task, $status) {
            $updateData = ['status' => $status];
            
            if ($status === 'completed') {
                $updateData['completed_at'] = now();
            } elseif ($task->status === 'completed' && $status !== 'completed') {
                $updateData['completed_at'] = null;
            }

            $task->update($updateData);
            return $task->fresh();
        });
    }

    public function addComment(Task $task, string $comment, User $user): TaskComment
    {
        return DB::transaction(function () use ($task, $comment, $user) {
            return TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'comment' => $comment,
            ]);
        });
    }

    public function addAttachment(Task $task, $file, User $user): TaskAttachment
    {
        return DB::transaction(function () use ($task, $file, $user) {
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('task-attachments/' . $task->id, 'public');
            $fileSize = $file->getSize();
            $fileType = $file->getMimeType();

            return TaskAttachment::create([
                'task_id' => $task->id,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'uploaded_by' => $user->id,
            ]);
        });
    }

    public function deleteAttachment(TaskAttachment $attachment): bool
    {
        return DB::transaction(function () use ($attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
            return $attachment->delete();
        });
    }
}


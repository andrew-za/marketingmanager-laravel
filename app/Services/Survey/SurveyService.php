<?php

namespace App\Services\Survey;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyDistribution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SurveyService
{
    public function createSurvey(array $data, User $user): Survey
    {
        return DB::transaction(function () use ($data, $user) {
            $survey = Survey::create([
                ...$data,
                'organization_id' => $user->primaryOrganization()->id,
                'created_by' => $user->id,
            ]);

            if (isset($data['questions']) && is_array($data['questions'])) {
                foreach ($data['questions'] as $index => $questionData) {
                    SurveyQuestion::create([
                        'survey_id' => $survey->id,
                        'question' => $questionData['question'],
                        'type' => $questionData['type'] ?? 'text',
                        'options' => $questionData['options'] ?? null,
                        'is_required' => $questionData['is_required'] ?? false,
                        'order' => $questionData['order'] ?? $index,
                    ]);
                }
            }

            return $survey->fresh();
        });
    }

    public function updateSurvey(Survey $survey, array $data): Survey
    {
        return DB::transaction(function () use ($survey, $data) {
            $survey->update($data);
            return $survey->fresh();
        });
    }

    public function deleteSurvey(Survey $survey): bool
    {
        return DB::transaction(function () use ($survey) {
            return $survey->delete();
        });
    }

    public function addQuestion(Survey $survey, array $questionData): SurveyQuestion
    {
        return DB::transaction(function () use ($survey, $questionData) {
            return SurveyQuestion::create([
                'survey_id' => $survey->id,
                'question' => $questionData['question'],
                'type' => $questionData['type'] ?? 'text',
                'options' => $questionData['options'] ?? null,
                'is_required' => $questionData['is_required'] ?? false,
                'order' => $questionData['order'] ?? $survey->questions()->max('order') + 1,
            ]);
        });
    }

    public function updateQuestion(SurveyQuestion $question, array $data): SurveyQuestion
    {
        return DB::transaction(function () use ($question, $data) {
            $question->update($data);
            return $question->fresh();
        });
    }

    public function deleteQuestion(SurveyQuestion $question): bool
    {
        return DB::transaction(function () use ($question) {
            return $question->delete();
        });
    }

    public function submitResponse(Survey $survey, array $responses, ?string $respondentEmail = null): array
    {
        return DB::transaction(function () use ($survey, $responses, $respondentEmail) {
            $createdResponses = [];

            foreach ($responses as $responseData) {
                $response = SurveyResponse::create([
                    'survey_id' => $survey->id,
                    'question_id' => $responseData['question_id'],
                    'respondent_email' => $respondentEmail,
                    'response' => is_array($responseData['response']) 
                        ? json_encode($responseData['response']) 
                        : $responseData['response'],
                ]);

                $createdResponses[] = $response;
            }

            $survey->increment('response_count');

            return $createdResponses;
        });
    }

    public function activateSurvey(Survey $survey): Survey
    {
        return DB::transaction(function () use ($survey) {
            $survey->update([
                'status' => 'active',
                'start_date' => $survey->start_date ?? now(),
            ]);
            return $survey->fresh();
        });
    }

    public function closeSurvey(Survey $survey): Survey
    {
        return DB::transaction(function () use ($survey) {
            $survey->update([
                'status' => 'closed',
                'end_date' => now(),
            ]);
            return $survey->fresh();
        });
    }

    public function createDistribution(Survey $survey, string $type, array $settings = []): SurveyDistribution
    {
        return SurveyDistribution::create([
            'survey_id' => $survey->id,
            'distribution_type' => $type,
            'distribution_key' => Str::random(32),
            'settings' => $settings,
        ]);
    }

    public function getAnalytics(Survey $survey, array $options = []): array
    {
        $startDate = $options['start_date'] ?? $survey->start_date;
        $endDate = $options['end_date'] ?? $survey->end_date ?? now();

        $responses = $survey->responses()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $questionAnalytics = [];
        foreach ($survey->questions as $question) {
            $questionResponses = $responses->where('question_id', $question->id);
            
            $questionAnalytics[$question->id] = [
                'question' => $question->question,
                'type' => $question->type,
                'response_count' => $questionResponses->count(),
                'responses' => $questionResponses->pluck('response')->toArray(),
            ];
        }

        return [
            'total_responses' => $responses->count(),
            'completion_rate' => $survey->response_count > 0 
                ? ($responses->count() / $survey->response_count) * 100 
                : 0,
            'question_analytics' => $questionAnalytics,
            'responses_by_date' => $responses->groupBy(function ($response) {
                return $response->created_at->format('Y-m-d');
            })->map->count(),
        ];
    }

    public function exportResponses(Survey $survey, string $format = 'csv'): string
    {
        $responses = $survey->responses()->with('question')->get();
        
        if ($format === 'csv') {
            $csv = "Question,Response,Respondent Email,Created At\n";
            foreach ($responses as $response) {
                $csv .= sprintf(
                    '"%s","%s","%s","%s"\n',
                    str_replace('"', '""', $response->question->question ?? ''),
                    str_replace('"', '""', is_array($response->response) ? json_encode($response->response) : $response->response),
                    $response->respondent_email ?? '',
                    $response->created_at->format('Y-m-d H:i:s')
                );
            }
            return $csv;
        }
        
        return json_encode($responses->toArray());
    }
}


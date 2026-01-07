<?php

namespace App\Http\Controllers\Survey;

use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\CreateSurveyRequest;
use App\Http\Requests\Survey\UpdateSurveyRequest;
use App\Http\Resources\Survey\SurveyResource;
use App\Models\Survey;
use App\Services\Survey\SurveyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SurveyController extends Controller
{
    public function __construct(
        private SurveyService $surveyService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $query = Survey::where('organization_id', $organizationId)
            ->with(['creator', 'questions']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $surveys = $query->orderBy('created_at', 'desc')->paginate();

        return SurveyResource::collection($surveys);
    }

    public function store(CreateSurveyRequest $request): JsonResponse
    {
        $survey = $this->surveyService->createSurvey(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new SurveyResource($survey),
            'message' => 'Survey created successfully.',
        ], 201);
    }

    public function show(Survey $survey): JsonResponse
    {
        $this->authorize('view', $survey);

        $survey->load(['creator', 'questions', 'responses']);

        return response()->json([
            'success' => true,
            'data' => new SurveyResource($survey),
        ]);
    }

    public function update(UpdateSurveyRequest $request, Survey $survey): JsonResponse
    {
        $this->authorize('update', $survey);

        $survey = $this->surveyService->updateSurvey(
            $survey,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new SurveyResource($survey),
            'message' => 'Survey updated successfully.',
        ]);
    }

    public function destroy(Survey $survey): JsonResponse
    {
        $this->authorize('delete', $survey);

        $this->surveyService->deleteSurvey($survey);

        return response()->json([
            'success' => true,
            'message' => 'Survey deleted successfully.',
        ]);
    }

    public function addQuestion(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('update', $survey);

        $request->validate([
            'question' => ['required', 'string'],
            'type' => ['required', 'in:text,textarea,radio,checkbox,select,rating,date'],
            'options' => ['nullable', 'array'],
            'is_required' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer'],
        ]);

        $question = $this->surveyService->addQuestion(
            $survey,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => $question,
            'message' => 'Question added successfully.',
        ], 201);
    }

    public function submitResponse(Request $request, Survey $survey): JsonResponse
    {
        $request->validate([
            'responses' => ['required', 'array', 'min:1'],
            'responses.*.question_id' => ['required', 'exists:survey_questions,id'],
            'responses.*.response' => ['required'],
            'respondent_email' => ['nullable', 'email'],
        ]);

        $responses = $this->surveyService->submitResponse(
            $survey,
            $request->responses,
            $request->respondent_email
        );

        return response()->json([
            'success' => true,
            'data' => $responses,
            'message' => 'Response submitted successfully.',
        ], 201);
    }

    public function activate(Survey $survey): JsonResponse
    {
        $this->authorize('update', $survey);

        $survey = $this->surveyService->activateSurvey($survey);

        return response()->json([
            'success' => true,
            'data' => new SurveyResource($survey),
            'message' => 'Survey activated successfully.',
        ]);
    }

    public function close(Survey $survey): JsonResponse
    {
        $this->authorize('update', $survey);

        $survey = $this->surveyService->closeSurvey($survey);

        return response()->json([
            'success' => true,
            'data' => new SurveyResource($survey),
            'message' => 'Survey closed successfully.',
        ]);
    }

    public function createDistribution(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('update', $survey);

        $request->validate([
            'distribution_type' => ['required', 'in:email,link,embed'],
            'settings' => ['nullable', 'array'],
        ]);

        $distribution = $this->surveyService->createDistribution(
            $survey,
            $request->distribution_type,
            $request->settings ?? []
        );

        return response()->json([
            'success' => true,
            'data' => $distribution,
            'message' => 'Distribution created successfully.',
        ], 201);
    }

    public function analytics(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('view', $survey);

        $analytics = $this->surveyService->getAnalytics($survey, [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    public function exportResponses(Request $request, Survey $survey)
    {
        $this->authorize('view', $survey);

        $format = $request->get('format', 'csv');
        $content = $this->surveyService->exportResponses($survey, $format);

        $filename = "survey_{$survey->id}_responses." . ($format === 'csv' ? 'csv' : 'json');

        return response($content)
            ->header('Content-Type', $format === 'csv' ? 'text/csv' : 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}


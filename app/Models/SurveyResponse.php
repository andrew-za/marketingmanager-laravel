<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'question_id',
        'respondent_email',
        'response',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class);
    }
}


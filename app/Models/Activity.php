<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'coordenador_id',
        'description',
        'category_id',
        'submitted_at',
        'status',
        'request_complementary_hours',
        'valid_complementary_hours',
        'occurrence_data'
    ];

    protected static function booted()
    {
        static::created(function ($activity) {
            $activity->createEvaluation();
        });

        static::updated(function ($activity) {

        });

        static::deleted(function ($activity) {


        });

        static::saved(function ($activity) {

        });
    }

    public function createEvaluation(){
        Evaluation::create([
        'user_id' => $this->user_id, // ou Auth::id()
        'activity_id' => $this->id,
        'decision' => 'pending_review',

        // outros campos  (criar depois)
    ]);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class,'coordenador_id');
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(ProgressLog::class);
    }
}

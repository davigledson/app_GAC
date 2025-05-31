<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    public $timestamps = false;

    protected $fillable = [
         'evaluation_id',
        'activity_id',
        'evaluator_id',
        'comments',
        'validated_hours',
        'created_at',
    ];
    protected $table = 'feedbacks';
    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
   }

 public function evaluation(): BelongsTo
{
    return $this->belongsTo(Evaluation::class);
}
}

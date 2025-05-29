<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Evaluation extends Model
{
    protected $fillable = [
        'activity_id',
        'evaluator_id',
        'decision',
        'evaluated_at',
    ];

    protected $casts = [
        'evaluated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($evaluation) {
            // Cria o feedback automaticamente
             dd($evaluation);

            $evaluation->updateActivityStatus();
        });

        static::updated(function ($evaluation) {
         
            $evaluation->updateActivityStatus();
        });

        static::deleted(function ($evaluation) {
             $evaluation->feedback()->delete();
        });
    }

   public function updateActivityStatus()
    {
        if ($this->activity) {
            switch ($this->decision) {
                case 'approved':
                    $status = 'approved';
                    break;
                case 'rejected':
                    $status = 'rejected';
                    break;
                case 'pending_review':
                    $status = 'pending';
                    break;
                default:
                    $status = $this->activity->status;
                    break;
            }

            $this->activity->updateQuietly(['status' => $status]);
        }
    }
  public function feedback(): HasOne
{
    // Relacionamento simples usando a chave estrangeira padrão
    return $this->hasOne(Feedback::class);
}

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}

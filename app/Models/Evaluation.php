<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
class Evaluation extends Model
{
    protected $fillable = [
        'activity_id',
        'evaluator_id',
        'decision',
        'evaluated_at',
    ];



    protected static function booted()
    {
        static::created(function ($evaluation) {
            // Cria o feedback automaticamente
            // dd($evaluation);

            $evaluation->updateActivityStatus();

        });

        static::updated(function ($evaluation) {

            $evaluation->updateActivityStatus();

             $evaluation->evaluated_at = Carbon::now();
              $evaluation->updateComplementaryHours();
              $evaluation->saveQuietly();

        });

        static::deleted(function ($evaluation) {
             $evaluation->feedback()->delete();
        });
    }

  public function updateActivityStatus()
    {
        if (!$this->activity) return;

        $status = match ($this->decision) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'pending',
        };

        $this->activity->updateQuietly(['status' => $status]);
    }

 public function updateComplementaryHours()
    {
        if ($this->decision !== 'approved' || !$this->activity || !$this->feedback) {
            return;
        }

        // Atualiza horas validadas na atividade
        $this->activity->updateQuietly([
            'valid_complementary_hours' => $this->feedback->validated_hours ?? 0
        ]);

        // Atualiza horas do usuário
        if ($this->activity->user) {
            $this->activity->user->increment(
                'paid_complementary_hours',
                $this->feedback->validated_hours ?? 0
            );
        }
    }

  public function feedback(): HasOne
{
    // Relacionamento simples usando a chave estrangeira padrão
    return $this->hasOne(Feedback::class);
}

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class)->with('user');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
     public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}

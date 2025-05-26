<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }



    public function createdCategories()
{
    return $this->hasMany(Category::class, 'created_by');
}

public function createdProjects()
{
    return $this->hasMany(Project::class, 'created_by');
}

public function activities()
{
    return $this->hasMany(Activity::class);
}

public function feedbacksGiven()
{
    return $this->hasMany(Feedback::class, 'evaluator_id');
}

public function evaluationsGiven()
{
    return $this->hasMany(Evaluation::class, 'evaluator_id');
}

public function progressLogs()
{
    return $this->hasMany(ProgressLog::class);
}

}

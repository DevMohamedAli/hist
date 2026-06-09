<?php

namespace Modules\User\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\Student;
use Modules\User\Database\Factories\UserFactory;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['name', 'email', 'avatar', 'password', 'email_verified_at'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{

    /** @use HasFactory<UserFactory> */
    use HasRoles, HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

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
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Create a new factory instance for the model.
     *
     * The model lives outside the default "App\Models" namespace, so the
     * module declares its factory explicitly instead of relying on Laravel's
     * convention-based factory discovery.
     */
    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function instructor()
    {
        return $this->hasOne(Instructor::class);
    }

    // or just define the morphMany:
    public function activities()
    {
        return $this->morphMany(Activity::class, 'causer');
    }
}

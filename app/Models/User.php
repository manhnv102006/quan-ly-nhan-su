<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['role_id', 'username', 'name', 'email', 'password', 'status', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory */
    use HasFactory, Notifiable;

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return $this->role && in_array($this->role->name, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isManager(): bool
    {
        return $this->hasRole(Role::MANAGER);
    }

    public function isEmployee(): bool
    {
        return $this->hasRole(Role::EMPLOYEE);
    }

    public function dashboardRouteName(): string
    {
        return $this->role?->dashboardRouteName() ?? 'dashboard';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
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
        ];
    }
}

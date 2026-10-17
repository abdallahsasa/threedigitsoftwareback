<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    protected $guarded = [];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'permissions' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return ($this->role ?? 'admin') === 'admin';
    }

    public function canViewAccounting(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return in_array('view_accounting', $this->permissions ?? []);
    }

    public function canCreateTransactions(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return in_array('create_transactions', $this->permissions ?? []);
    }

    public function canManageReconciliations(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return in_array('manage_reconciliations', $this->permissions ?? []);
    }
}

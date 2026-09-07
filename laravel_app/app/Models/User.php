<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['admin', 'seller']);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is seller
     */
    public function isSeller(): bool
    {
        return $this->hasRole('seller');
    }

    /**
     * Check if user is buyer
     */
    public function isBuyer(): bool
    {
        return $this->hasRole('buyer');
    }

    /**
     * Get seller application if exists
     */
    public function sellerApplication()
    {
        return $this->hasOne(SellerApplication::class);
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    public function initials(): string
    {
        $name = $this->name ?? '';

        // Trim and collapse whitespace
        $name = preg_replace('/\s+/', ' ', trim($name));

        if ($name === '') {
            return 'U';
        }

        $parts = explode(' ', $name);

        // take first letter of first two words
        $letters = [];
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $letters[] = mb_strtoupper(mb_substr($part, 0, 1));
            if (count($letters) >= 2) {
                break;
            }
        }

        return implode('', $letters);
    }

    /**
     * Allow using $user->initials as attribute as well.
     *
     * @return string
     */
    public function getInitialsAttribute(): string
    {
        return $this->initials();
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'phone_number',
        'password',
        'status',
        'email_verification_token',
        'email_verified_at',
        'otp',
        'otp_expires_at'
    ];


    // The Documents that belongs to the User

    public function documents(): BelongsToMany
    {
        // return $this->belongsToMany(Document::class, 'document_requests');
                    // ->withPivot('status')
                    // ->withTimestamps();

        return $this->belongsToMany(Document::class);
    }
}


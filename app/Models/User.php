<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fname',
        'lname',
        'email',
        'password',
        'otp_token',
        'otp_sent_at',
        'reset_password',
        'phone',
        'image',
        'country',
        'time_zone',
        'bio',
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
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['name'];

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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    /**
     * Accessor to build a full name from fname and lname.
     */
    public function getNameAttribute(): string
    {
        $first = trim((string)($this->fname ?? ''));
        $last = trim((string)($this->lname ?? ''));
        $full = trim($first . ' ' . $last);
        if ($full !== '') {
            return $full;
        }
        // Fallback to email or phone if name parts are missing
        return (string)($this->email ?? $this->phone ?? '');
    }

    public function brands()
    {
        return $this->hasMany(BrandChat::class, 'user_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'user_id', 'id');
    }

    public function favorites()
    {
        return $this->belongsToMany(BrandChat::class, 'brand_name_favorites', 'user_id', 'brand_chat_id');
    }

    public static function updateOrCreateSocialUser($provider, $socialUser)
    {

        // Check if user exists by provider ID
        $user = self::where(["provider" =>$provider,"provider_id" => $socialUser->getId()])->first();

        if ($user) {
            return $user;
        }

        // Check if user exists by email
        $user = self::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Link existing account to social provider
            $user->update(["provider_id" => $socialUser->getId()]);
            return $user;
        }

        // Create new user
        return self::create([
            'fname' =>  $socialUser->user['given_name'] ?? $socialUser->getName() ?? $socialUser->getEmail(),
            'lname' => $socialUser->user['family_name']  ?? $socialUser->get() ?? $socialUser->getEmail(),
            'email' => $socialUser->getEmail(),
            'provider' => $provider,
            "provider_id" => $socialUser->getId(),
            'image' => $socialUser->getAvatar(),
            'email_verified_at' => now(), // Social users are verified
        ]);
    }
}

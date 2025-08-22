<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stripe\Customer;
use Stripe\Stripe;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'city_id', 
        'avatar',
        'password',
        'status',
        'otp_code',
        'otp_expires_at',
        'security_token',
        'phone_verified_at',
        'email_verified_at',
        'stripe_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'stripe_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include inactive users.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Get the city that the user belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the farms that the user has favorited.
     */
    public function favoriteFarms(): BelongsToMany
    {
        return $this->belongsToMany(Farm::class, 'favorite_farms', 'user_id', 'farm_id')
                    ->withTimestamps();
    }

    /**
     * Get the user's bookings.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(FarmBooking::class);
    }

    /**
     * Create or get Stripe customer
     */
    public function createOrGetStripeCustomer(): string
    {
        if ($this->stripe_id) {
            return $this->stripe_id;
        }

        if (empty($this->email) && empty($this->phone)) {
            throw new \Exception('Either email or phone number is required to create a payment account. Please update your profile.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            
            $customerData = [
                'metadata' => [
                    'user_id' => $this->id,
                ],
            ];

            if (!empty($this->email)) {
                $customerData['email'] = $this->email;
            }

            if (!empty($this->phone)) {
                $customerData['phone'] = $this->phone;
            }

            if (!empty($this->name)) {
                $customerData['name'] = $this->name;
            }

            if (!empty($this->city_id)) {
                $customerData['metadata']['city_id'] = $this->city_id;
            }

            $customer = Customer::create($customerData);
            $this->update(['stripe_id' => $customer->id]);

            return $customer->id;

        } catch (\Exception $e) {
            \Log::error('Failed to create Stripe customer', [
                'user_id' => $this->id,
                'has_email' => !empty($this->email),
                'has_phone' => !empty($this->phone),
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Failed to create payment customer account: ' . $e->getMessage());
        }
    }

    /**
     * Check if user can create a Stripe customer
     */
    public function canCreateStripeCustomer(): bool
    {
        return !empty($this->email) || !empty($this->phone);
    }

    /**
     * Check if user has Stripe customer account
     */
    public function hasStripeAccount(): bool
    {
        return !empty($this->stripe_id);
    }

    /**
     * Get available contact methods
     */
    public function getAvailableContactMethods(): array
    {
        $methods = [];
        
        if (!empty($this->email)) {
            $methods[] = 'email';
        }
        
        if (!empty($this->phone)) {
            $methods[] = 'phone';
        }
        
        return $methods;
    }

    /**
     * Get missing contact info
     */
    public function getMissingContactInfo(): array
    {
        $missing = [];
        
        if (empty($this->email)) {
            $missing[] = 'email';
        }
        
        if (empty($this->phone)) {
            $missing[] = 'phone';
        }
        
        return $missing;
    }
}
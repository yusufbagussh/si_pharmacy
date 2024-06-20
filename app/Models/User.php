<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'pgsql_second';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
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

    /**
     * Kita override boot method
     *
     * Mengisi primary key secara otomatis dengan UUID ketika membuat record
     */
    // protected static function boot()
    // {
    //     parent::boot();
    //     static::creating(function ($model) {
    //         if (empty($model->{$model->getKeyName()})) {
    //             $model->{$model->getKeyName()} = Str::uuid()->toString();
    //         }
    //     });
    // }

    /**
     * Kita override getIncrementing method
     *
     * Menonaktifkan auto increment
     */
    // public function getIncrementing()
    // {
    //     return false;
    // }

    /**
     * Kita override getKeyType method
     *
     * Memberi tahu laravel bahwa model ini menggunakan primary key bertipe string
     */
    // public function getKeyType()
    // {
    //     return 'string';
    // }

    /**
     * receipts
     *
     * @return void
     */
    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function checkPasswordByUsername($username)
    {
        $user = User::where('username', $username)->first();
        return $user;
    }
}

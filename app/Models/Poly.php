<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Poly extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Kita override boot method
     *
     * Mengisi primary key secara otomatis dengan UUID ketika membuat record
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    /**
     * Kita override getIncrementing method
     *
     * Menonaktifkan auto increment
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Kita override getKeyType method
     *
     * Memberi tahu laravel bahwa model ini menggunakan primary key bertipe string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * receipts
     *
     * @return void
     */
    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    /**
     * Get the receipts for the poly with a specific status.
     */
    public function receiptsByStatusProcess()
    {
        // $polies = Poly::with(['receipts' => function (Builder $query) {
        //     $query->where('status', 'proses');
        // }])->get();
        return $this->hasMany(Receipt::class)->where('status', 'process');
    }

    /**
     * Get the receipts for the poly with a specific status.
     */
    public function receiptsByStatusUndelivered()
    {
        return $this->hasMany(Receipt::class)->where('status', '!=', 'delivered');
    }
}

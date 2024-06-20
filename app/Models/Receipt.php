<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Receipt extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'receipts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code_order',
        'queue',
        'patient_name',
        'description_receipt',
        'user_id',
        'docter_id',
        'status',
        'poly_id'
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
     * post
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * post
     *
     * @return void
     */
    public function docter()
    {
        return $this->belongsTo(Docter::class);
    }

    /**
     * post
     *
     * @return void
     */
    public function poly()
    {
        return $this->belongsTo(Poly::class);
    }
}

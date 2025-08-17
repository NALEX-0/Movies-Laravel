<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMovie extends Model
{
    use HasFactory;
    protected $table = 'user_movies';
    protected $fillable = [
        'user_id',
        'movie_id',
        'date_watched',
        'rating',
    ];
    protected $casts = [
        'date_watched' => 'date',
        'rating'       => 'decimal:1',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id');
    }
}

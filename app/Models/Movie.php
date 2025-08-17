<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Movie extends Model
{
    use HasFactory;
    protected $table = 'movies';
    protected $fillable = [
        'tmdb_id',
        'title',
        'year',
        'poster_url',
    ];
    protected $casts = [
        'year'       => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_movies', 'movie_id', 'user_id')
            ->withPivot(['date_watched', 'rating'])
            ->withTimestamps();
    }

    public function userMovies()
    {
        return $this->hasMany(UserMovie::class, 'movie_id');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class MoviesController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $movies = $user->movies()
            ->select('movies.*')
            ->orderBy('user_movies.created_at', 'desc')
            ->paginate(15);

        return view('movies.index', compact('movies'));
    }

    // public function search(Request $request): JsonResponse
    // {
    //     $q = trim($request->query('q', ''));
    //     if ($q === '') {
    //         return response()->json(['results' => []]);
    //     }

    //     $response = Http::get('https://api.themoviedb.org/3/search/movie', [
    //         'api_key'       => config('services.tmdb.key'),
    //         'query'         => $q,
    //         'include_adult' => false,
    //         'language'      => 'en-US',
    //         'page'          => 1,
    //     ])->throw();

    //     $results = collect($response->json('results', []))
    //         ->map(function ($m) {
    //             return [
    //                 'tmdb_id'     => $m['id'],
    //                 'title'       => $m['title'] ?? '',
    //                 'year'        => !empty($m['release_date']) ? substr($m['release_date'], 0, 4) : null,
    //                 'poster_url'  => !empty($m['poster_path']) ? 'https://image.tmdb.org/t/p/w185' . $m['poster_path'] : null,
    //                 'poster_full' => !empty($m['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $m['poster_path'] : null,
    //             ];
    //         })
    //         ->values();

    //     return response()->json(['results' => $results]);
    // }

    public function search(Request $request): JsonResponse
{
    $q = trim($request->query('q', ''));
    if ($q === '') {
        return response()->json(['results' => []]);
    }

    $key = (string) config('services.tmdb.key', '');
    if ($key === '') {
        return response()->json([
            'message' => 'TMDB_API_KEY not configured. Add it to .env and config/services.php.',
        ], 500);
    }

    $isV4 = Str::startsWith($key, 'eyJ'); // v4 tokens look like JWTs

    $http = Http::timeout(10)->acceptJson();
    $url  = 'https://api.themoviedb.org/3/search/movie';

    $params = [
        'query'         => $q,
        'include_adult' => false,
        'language'      => 'en-US',
        'page'          => 1,
    ];

    if ($isV4) {
        // v4: send Authorization: Bearer <token>
        $http = $http->withToken($key);
    } else {
        // v3: send ?api_key=<key>
        $params['api_key'] = $key;
    }

    try {
        $response = $http->get($url, $params);

        if ($response->failed()) {
            Log::warning('TMDB search failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json([
                'message' => 'TMDB request failed',
                'status'  => $response->status(),
            ], $response->status());
        }

        $results = collect($response->json('results', []))
            ->map(function ($m) {
                return [
                    'tmdb_id'     => $m['id'] ?? null,
                    'title'       => $m['title'] ?? '',
                    'year'        => !empty($m['release_date']) ? substr($m['release_date'], 0, 4) : null,
                    'poster_url'  => !empty($m['poster_path']) ? 'https://image.tmdb.org/t/p/w185' . $m['poster_path'] : null,
                    'poster_full' => !empty($m['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $m['poster_path'] : null,
                ];
            })
            ->filter(fn ($m) => !is_null($m['tmdb_id']))
            ->values();

        return response()->json(['results' => $results]);
    } catch (\Throwable $e) {
        Log::error('TMDB exception', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Server error contacting TMDB'], 500);
    }
}


    public function store(Request $request)
    {
        $validated = $request->validate([
            'tmdb_id'      => 'required|string|max:255',
            'title'        => 'required|string|max:255',
            'year'         => 'nullable|integer|min:1870|max:2100',
            'poster_url'   => 'nullable|string|max:255',
            'date_watched' => 'nullable|date',
            'rating'       => 'nullable|numeric|min:0|max:10',
        ]);

        // Upsert movie by tmdb_id
        $movie = Movie::firstOrCreate(
            ['tmdb_id' => $validated['tmdb_id']],
            [
                'title'      => $validated['title'],
                'year'       => $validated['year'] ?? null,
                'poster_url' => $validated['poster_url'] ?? null,
            ]
        );

        $user = $request->user();

        // Attach if not attached, then update pivot fields
        // $user->movies()->syncWithoutDetaching([$movie->id => []]);
        // $user->movies()->updateExistingPivot($movie->id, [
        //     'date_watched' => $validated['date_watched'] ?? null,
        //     'rating'       => $validated['rating'] ?? null,
        // ]);

        // Check if the user already has this movie
        $alreadyExists = $user->movies()->where('movie_id', $movie->id)->exists();

        if ($alreadyExists) {
            return back()->withErrors([
                'tmdb_id' => 'You have already added this movie to your list.',
            ])->withInput();
        }

        // Attach and set pivot fields
        $user->movies()->attach($movie->id, [
            'date_watched' => $validated['date_watched'] ?? null,
            'rating'       => $validated['rating'] ?? null,
        ]);




        return back()->with('status', 'Movie added!');
    }

}

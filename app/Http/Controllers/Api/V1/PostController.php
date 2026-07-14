<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\BlogPost;
use Carbon\Carbon;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $posts = BlogPost::with('category')
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->get();

        $data = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'category' => $post->category ? $post->category->name : null,
                'date' => Carbon::parse($post->published_at)->format('M d, Y'),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function show(Request $request, $slug)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $post = BlogPost::with('category')
            ->where('status', 'published')
            ->where(function($q) use ($slug) {
                $q->where('slug->en', $slug)->orWhere('slug->ar', $slug);
            })
            ->firstOrFail();

        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'category' => $post->category ? $post->category->name : null,
            'date' => Carbon::parse($post->published_at)->format('M d, Y'),
        ];

        return response()->json(['data' => $data]);
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = Blog::where('status', 'published')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(9);

        return view('web.blog.index', compact('posts'));
    }

    public function show(string $slug): View
    {
        $post = Blog::where('slug', $slug)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->with('author', 'seoMeta')
            ->firstOrFail();

        $related = Blog::where('status', 'published')
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('web.blog.show', compact('post', 'related'));
    }
}
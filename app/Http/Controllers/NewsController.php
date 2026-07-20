<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::published()
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('news.index', compact('news'));
    }

    public function show($slug)
    {
        $article = News::where('slug', $slug)->firstOrFail();

        if ($article->status !== 'published' && !Auth::check()) {
            abort(404);
        }

        $article->increment('views');

        $related = News::published()
            ->where('id', '!=', $article->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('news.show', compact('article', 'related'));
    }

    public function create()
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        return view('news.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:10240',
            'status' => 'required|in:draft,published',
        ]);

        $validated['slug'] = News::generateSlug($validated['title']);
        $validated['author_id'] = Auth::id();
        $validated['published_at'] = $validated['status'] === 'published' ? now() : null;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('news', 'public');
        } else {
            unset($validated['image']);
        }

        $article = News::create($validated);

        return redirect()->route('news.show', $article->slug)
            ->with('success', 'News article published successfully!');
    }

    public function edit($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $article = News::findOrFail($id);
        return view('news.edit', compact('article'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $article = News::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:10240',
            'status' => 'required|in:draft,published',
        ]);

        if ($request->hasFile('image')) {
            if ($article->image && $article->image !== '0') {
                Storage::disk('public')->delete($article->image);
            }
            $validated['image'] = $request->file('image')->store('news', 'public');
        } else {
            unset($validated['image']);
        }

        if ($validated['status'] === 'published' && !$article->published_at) {
            $validated['published_at'] = now();
        }

        $article->update($validated);

        return redirect()->route('news.show', $article->slug)
            ->with('success', 'News article updated successfully!');
    }

    public function destroy($id)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $article = News::findOrFail($id);

        if ($article->image && $article->image !== '0') {
            Storage::disk('public')->delete($article->image);
        }

        $article->delete();

        return redirect()->route('news.index')
            ->with('success', 'News article deleted.');
    }

    public function adminIndex()
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $news = News::orderByDesc('created_at')->paginate(20);
        return view('news.admin-index', compact('news'));
    }
}

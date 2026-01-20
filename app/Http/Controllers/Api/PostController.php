<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        return Post::with('owner','featuredMedia')->paginate();
    }

    public function show($id)
    {
        return Post::with('owner','featuredMedia')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->only(['user_id','website_id','title','slug','content','status','published_at']);
        $post = Post::create($data);
        return response()->json($post, 201);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $post->update($request->all());
        return $post;
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response()->noContent();
    }
}

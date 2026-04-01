<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::query();

        if ($request->has('jenis') && $request->jenis != '') {
            $query->where('jenis', $request->jenis);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%$search%")
                    ->orWhere('konten', 'like', "%$search%");
            });
        }

        // Sorting
        $sortBy = $request->get('sortBy', 'created_at');
        $sortDesc = $request->boolean('sortDesc', true);
        $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');

        // Pagination
        $perPage = $request->get('itemsPerPage', 10);
        $pages = $query->paginate($perPage);

        return response()->json($pages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'tanggal' => 'required|date',
            'jenis' => 'required|in:1,2,3',
            'status' => 'required|boolean',
        ]);

        $news = News::create([
            'judul' => $request->judul,
            'slug' => Str::slug($request->judul) . '-' . Str::random(5),
            'konten' => $request->konten,
            'tanggal' => $request->tanggal,
            'jenis' => $request->jenis,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'News created', 'data' => $news], 201);
    }

    public function show($id)
    {
        $news = News::findOrFail($id);
        return response()->json($news);
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'tanggal' => 'required|date',
            'jenis' => 'required|in:1,2,3',
            'status' => 'required|boolean',
        ]);

        $news->update($request->all());

        return response()->json(['message' => 'News updated']);
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete();

        return response()->json(['message' => 'News deleted']);
    }

    public function toggleStatus(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $news->status = $request->status;
        $news->save();

        return response()->json([
            'message' => 'Status updated',
            'status'  => $news->status
        ]);
    }

    public function news()
    {
        $data = News::where('jenis', 1)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function info()
    {
        $limit = 5; // bisa diganti dari setting database jika ada
        $data = News::where('jenis', 2)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function masjid()
    {
        $limit = 5; // bisa diganti dari setting database jika ada
        $data = News::where('jenis', 3)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $data
        ], 200);
    }
}

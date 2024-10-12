<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Gallery\Image;
use App\Http\Controllers\Controller;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('backend.image.index', [
            'images' => Image::latest()->get(
                ['id', 'name', 'slug', 'description', 'file', 'created_at']
            )
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.image.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|min:3',
            'description' => 'required|min:3',
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/svg|max:2048'
        ]);

        try {
            $data['file'] = $request->file('file')->store('images', 'public');

            $data['uuid'] = Str::uuid();
            $data['slug'] = Str::slug($data['name']);
            Image::create($data);

            return redirect()->route('panel.image.index')->with('success', 'Image created successfully');
        } catch (\Exception $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

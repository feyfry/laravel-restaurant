<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageRequest;
use App\Http\Services\FileService;
use App\Http\Services\ImageService;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct(private FileService $fileService, private ImageService $imageService)
    {

    }
    public function index()
    {
        return view('backend.image.index', [
            'images' => $this->imageService->select(10),
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
    public function store(ImageRequest $request)
    {
        $data = $request->validated();

        try {
            $data['file'] = 'images/' . $this->fileService->upload($request->file('file'), 'images');

            $this->imageService->create($data);

            return redirect()->route('panel.image.index')->with('success', 'Image created successfully');
        } catch (\Exception $error) {
            $this->fileService->delete($data['file'], 'images');
            return redirect()->back()->with('error', $error->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        return view('backend.image.show', [
            'image' => $this->imageService->selectFirstBy('uuid', $uuid)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uuid)
    {
        return view('backend.image.edit', [
            'image' => $this->imageService->selectFirstBy('uuid', $uuid),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ImageRequest $request, string $uuid)
    {
        $data = $request->validated();

        $image = $this->imageService->selectFirstBy('uuid', $uuid);

        try {
            if ($request->hasFile('file')) {
                // Hapus file gambar lama
                $this->fileService->delete($image->file, 'images');

                // Lalu Upload file gambar baru
                $data['file'] = 'images/' . $this->fileService->upload($request->file('file'), 'images');
            }
            else {
                // jika tidak upload
                $data['file'] = $image->file;
            }

            // Update data
            $this->imageService->update($uuid, $data);

            return redirect()->route('panel.image.index')->with('success', 'Image updated successfully');
        } catch (\Exception $error) {

            $this->fileService->delete($data['file'], 'images');

            return redirect()->back()->with('error', $error->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $image = $this->imageService->selectFirstBy('uuid', $uuid);
        $this->fileService->delete($image->file, 'images');
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully']);
    }
}

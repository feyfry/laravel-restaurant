<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\VideoRequest;
use App\Http\Services\VideoService;
use App\Models\Video;

class VideoController extends Controller
{

    public function __construct(private VideoService $videoService)
    {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('backend.video.index', [
            'videos' => Video::latest()->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.video.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VideoRequest $request)
    {
        $videos = $request->validated();

        try {
            $this->videoService->create($videos);

            return redirect()->route('panel.video.index')->with('success', 'Video created successfully');
        } catch (\Exception $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        return view('backend.video.show', [
            'video' => $this->videoService->selectFirstBy('uuid', $uuid),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uuid)
    {
        return view('backend.video.edit', [
            'video' => $this->videoService->selectFirstBy('uuid', $uuid),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VideoRequest $request, string $uuid)
    {
        $video = $request->validated();

        try {
            $this->videoService->update($uuid, $video);

            return redirect()->route('panel.video.index')->with('success', 'Video updated successfully');
        } catch (\Exception $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $video = $this->videoService->selectFirstBy('uuid', $uuid);
        $this->videoService->delete($video->uuid);
        return response()->json(['message' => 'Video deleted successfully']);
    }
}

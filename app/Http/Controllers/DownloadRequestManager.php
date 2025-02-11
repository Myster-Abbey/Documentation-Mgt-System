<?php

namespace App\Http\Controllers;

use App\Helper\General;
use App\Models\Document;
use App\Models\DownloadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DownloadRequestManager extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index()
    {
        $requests = DownloadRequest::with(['document', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return General::apiSuccessResponse('Download requests retrieved', 200, $requests);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file_id' => 'required|exists:documents,id'
        ]);

        $downloadRequest = DownloadRequest::create([
            'file_id' => $request->file_id,
            'user_id' => Auth::id(),
            'status' => 'pending'
        ]);

        return General::apiSuccessResponse('Download request created', 200, $downloadRequest->load(['document', 'user']));
    }

    public function show($id)
    {
        $downloadRequest = DownloadRequest::with(['document', 'user'])->findOrFail($id);
        return General::apiSuccessResponse('Download request details', 200, $downloadRequest);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,archived'
        ]);

        $downloadRequest = DownloadRequest::findOrFail($id);
        $downloadRequest->update($request->only(['status']));

        return General::apiSuccessResponse('Request updated', 200, $downloadRequest->load(['document', 'user']));
    }

    public function archive($id)
    {
        $request = DownloadRequest::findOrFail($id);
        $request->update(['status' => 'archived']);

        return General::apiSuccessResponse('Request archived', 200, $request->load(['document', 'user']));
    }

    public function cancel($id)
    {
        $request = DownloadRequest::findOrFail($id);
        $request->delete();

        return General::apiSuccessResponse('Request cancelled', 200);
    }
}

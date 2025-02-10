<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DownloadRequest;
use Illuminate\Http\Request;

class DownloadRequestManager extends Controller
{
    //
    public function store(Request $request) {
        $request->validate(['file_id' => 'required']);

        $downloadRequest = DownloadRequest::create(['file_id' => $request->file_id, 'status' => 'pending']);
        return response()->json(['message' => 'Download request created', 'request' => $downloadRequest]);
    }

    public function archive($id) {
        $request = DownloadRequest::findOrFail($id);
        $request->update(['status' => 'archived']);
        return response()->json(['message' => 'Request archived']);
    }

    public function update(Request $request, $id) {
        $downloadRequest = DownloadRequest::findOrFail($id);
        $downloadRequest->update($request->all());
        return response()->json(['message' => 'Request updated']);
    }

    public function cancel($id) {
        DownloadRequest::findOrFail($id)->delete();
        return response()->json(['message' => 'Request cancelled']);
    }
}

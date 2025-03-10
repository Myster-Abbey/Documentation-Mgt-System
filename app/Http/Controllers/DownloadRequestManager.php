<?php

namespace App\Http\Controllers;

use App\helper\General;
use App\Http\Resources\DownloadRequestResource;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentRequest;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\Validator;

class DownloadRequestManager extends Controller
{

    public function index(Request $request)
    {
        try {
            Log::info('Index method called', ['user_id' => Auth::id()]);
            // $requests = DocumentRequest::with(['documents', 'users'])
            //     ->orderBy('created_at', 'desc')
            //     ->get();
            $requests=DocumentRequest::orderBy('created_at','desc')->get();
            $formatted_request = DownloadRequestResource::collection($requests);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Accessed index method'
            ]);

            return General::apiSuccessResponse('Download requests retrieved', 200, $formatted_request);
        } catch (\Throwable $th) {
            Log::error('Error retrieving download requests: ' . $th->getMessage());
            return General::apiFailureResponse('Error retrieving download requests', 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Store method called', ['user_id' => Auth::id(), 'request_data' => $request->all()]);
        $validator = Validator::make($request->all(), [
            'document_name' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            if ($validator->fails()) {
                return General::apiFailureResponse($validator->errors()->first(), 400);
            }

            Log::info('Store method called', ['user_id' => Auth::id(), 'request_data' => $request->all()]);

            $document = Document::where('name', $request->document_name)->first();
            if (!$document) {
                return General::apiFailureResponse('Document not found', 404);
            }

            $downloadRequest = DocumentRequest::create([
                'document_id' => $document->id,
                'user_id' => $request->user_id,
                'status' => 'pending'
            ]);

            AuditLog::create([
                'user_id' => $request->user_id,
                'action' => 'Document Requested: ' . $document->id
            ]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Stored a download request'
            ]);

            $available_requests = DocumentRequest::query()->where('user_id', $request->user_id)->get();

            return General::apiSuccessResponse('Download request created', 200, $available_requests);
        } catch (\Throwable $th) {
            Log::info("STORING DOCUMENT: " . $th->getMessage());
            return General::apiFailureResponse('Sorry, an error occurred', 405);
        }
    }

    public function show($id)
    {
        try {
            Log::info('Show method called for ID: ' . $id, ['user_id' => Auth::id()]);
            $downloadRequest = DocumentRequest::with(['documents', 'users'])->findOrFail($id);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Viewed download request ID: ' . $id
            ]);

            return General::apiSuccessResponse('Download request details', 200, $downloadRequest);
        } catch (\Throwable $th) {
            Log::error('Error retrieving download request: ' . $th->getMessage());
            return General::apiFailureResponse('Download request not found', 404);
        }
    }

    public function update(Request $request, $id)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,cancelled,archived'
        ]);

        if ($validator->fails()) {
            return General::apiFailureResponse($validator->errors()->first(), 400);
        }

        try {
            Log::info('Update method called for ID: ' . $id, ['user_id' => Auth::id(), 'request_data' => $request->all()]);


            $downloadRequest = DocumentRequest::find($id);

            if (!$downloadRequest) {
                return General::apiFailureResponse("The download request with id $id cannot be found", 404);
            }

            $downloadRequest->update([
                'status' => $request->status
            ]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Updated download request ID: ' . $id . ' to status: ' . $request->status
            ]);

            return General::apiSuccessResponse('Request updated', 200, $downloadRequest->load(['documents', 'users']));
        } catch (\Throwable $th) {
            Log::error('Error updating download request: ' . $th->getMessage());
            return General::apiFailureResponse('Error updating request', 500);
        }
    }

    public function archive($id)
    {
        try {
            Log::info('Archive method called for ID: ' . $id, ['user_id' => Auth::id()]);
            $request = DocumentRequest::findOrFail($id);
            $request->update(['status' => 'archived']);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Archived download request ID: ' . $id
            ]);

            return General::apiSuccessResponse('Request archived', 200, $request->load(['document', 'user']));
        } catch (\Throwable $th) {
            Log::error('Error archiving download request: ' . $th->getMessage());
            return General::apiFailureResponse('Error archiving request', 500);
        }
    }

    public function cancel($id)
    {
        try {
            Log::info('Cancel method called for ID: ' . $id, ['user_id' => Auth::id()]);
            $request = DocumentRequest::findOrFail($id);
            $request->delete();

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Cancelled download request ID: ' . $id
            ]);

            return General::apiSuccessResponse('Request cancelled', 200);
        } catch (\Throwable $th) {
            Log::error('Error cancelling download request: ' . $th->getMessage());
            return General::apiFailureResponse('Error cancelling request', 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Document;
use App\helper\General;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentManager extends Controller
{
    public function upload(Request $request) {
        // if (!auth()->user()->is_admin) {
        //     return General::apiFailureResponse('Unauthorized access', 403);
        // }
        $user = User::query()->find($request->user_id);
        $role = null;
        if ($user) {
            $role = $user->role;
        }

        if (strtolower($role) != 'admin') {
            return General::apiFailureResponse('You do not have permission', 401);
        }

        $validator = Validator::make($request->all(), [
            'document' => 'required|file|mimes:pdf|max:2048',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        // return 'dsdddf';
        if ($validator->fails()) {
            // return General::apiFailureResponse('hhhhhhhh', 422);
            return General::apiFailureResponse($validator->errors()->first(), 422);
        }

        try {
            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('documents'), $fileName);

            $document = Document::create([
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => public_path('documents/') . $fileName,
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Uploaded document: ' . $request->title
            ]);

            return General::apiSuccessResponse('File uploaded successfully', 201, $document);
        } catch (\Exception $e) {
            Log::info("UPLOAD ERROR: " . $e->getMessage() . ", LINE NUMBER: " . $e->getLine());
            return General::apiFailureResponse('Error uploading file', 500);
        }
    }

    public function show($id) {
        try {
            $document = Document::findOrFail($id);

            if (!auth()->user()) {
                return General::apiFailureResponse('Unauthorized access', 403);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Viewed document: ' . $document->title
            ]);

            return response()->file(storage_path("app/public/{$document->file_path}"));
        } catch (\Exception $e) {
            return General::apiFailureResponse('Document not found', 404);
        }
    }

    public function print($id) {
        try {
            $document = Document::findOrFail($id);

            if (!auth()->user()->is_admin) {
                return General::apiFailureResponse('Unauthorized access', 403);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Printed document: ' . $document->title
            ]);

            return response()->file(storage_path("app/public/{$document->file_path}"), [
                'Content-Disposition' => 'attachment; filename="' . $document->title . '.pdf"'
            ]);
        } catch (\Exception $e) {
            return General::apiFailureResponse('Document not found', 404);
        }
    }

    public function index() {
        // return 'hello';
        try {
            $documents = Document::query()
                ->select('id', 'title', 'description', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return General::apiSuccessResponse('Documents retrieved successfully', 200, $documents);
        } catch (\Exception $e) {
            return General::apiFailureResponse('Error retrieving documents', 500);
        }
    }
}

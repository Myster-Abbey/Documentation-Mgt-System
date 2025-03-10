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
    public function upload(Request $request)
    {
        Log::info("Upload method called by User ID: " . $request->user_id);
        $user = User::query()->find($request->user_id); // Get the authenticated user
        // echo "\nHELLO USER: " . json_encode($user) ;
        if (!$user || strtolower($user->role) != 'admin') {
            return General::apiFailureResponse('You do not have permission', 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', // Validate name
            'document' => 'required|file|mimes:pdf|max:2048', // Validate document
        ]);

        if ($validator->fails()) {
            return General::apiFailureResponse($validator->errors()->first(), 422);
        }

        try {
            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            // if (!is_dir(storage_path('app/public/documents/'))) {
            //     mkdir(storage_path('app/public/documents/'), 0755, true);
            //     // chown(storage_path('documents/'), 'www-data');
            // }
            $newFileName = str_replace(' ', '_', $fileName);

            $path = storage_path('app/public/documents/');

            $file->move($path, $newFileName);
            // echo $full_path;
            // Create the document with name and file_path
            $myFilePath = url('/') . '/storage/documents/' . $newFileName;
            $document = Document::create([
                'name' => $request->name, // Use name from request
                'file_path' => $myFilePath, // Store the relative path
                // 'file_path' => $path . $fileName, // Store the relative path
                'uploaded_by' => $user->id // Associate the document with the user
            ]);

            // chown(storage_path("documents/" . $fileName), 'www-data');

            // Log the upload action in laravel.log
            Log::info("File uploaded successfully: " . $document->name . " by User ID: " . $user->id);

            // Log the upload action in AuditLog
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Uploaded document: ' . $request->name
            ]);

            $all_docs = Document::query()->get();

            $response = [
                'uploaded_doc_id' => $document->id, // Use name from request
                'docs' => $all_docs, // Use name from request
                'file_path' => $myFilePath, // Store the relative path
                // 'file_path' => $path . $fileName, // Store the relative path
                'uploaded_by' => $user->id
            ];

            return General::apiSuccessResponse('File uploaded successfully', 201, $response);
        } catch (\Exception $e) {
            Log::info("UPLOAD ERROR: " . $e->getMessage() . ", LINE NUMBER: " . $e->getLine());
            return General::apiFailureResponse('Error uploading file', 500);
        }
    }

    public function show(Request $request,$id)
    {
        Log::info("Show method called for Document ID: " . $id . " by User ID: " . auth()->id());
        try {
            $document = Document::findOrFail($id);
            $user = User::query()->find($request->user_id); // Get the authenticated user
                    // echo "\nHELLO USER: " . json_encode($user);
                    if (!$user || strtolower($user->role) != 'admin') {
                        return General::apiFailureResponse('You do not have permission', 401);
                    }

            // if (!auth()->user()) {
            //     return General::apiFailureResponse('Unauthorized access', 403);
            // }

            // Log the view action in laravel.log
            Log::info("Document viewed successfully: " . $document->name . " by User ID: " . auth()->id());

            // Log the view action in AuditLog
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Viewed document: ' . $document->name
            ]);

            return response()->file(public_path($document->file_path));
        } catch (\Exception $e) {
            return General::apiFailureResponse('Document not found', 404);
        }
    }

    // public function print(Request $request, $id)
    // {
    //     try {
    //         $document = Document::findOrFail($id);
    //         $user = User::find($request->user_id)
    //         if (!$user || strtolower($user->role) != 'admin') {
    //             return General::apiFailureResponse('You do not have permission', 401);
    //         }

    //         AuditLog::create([
    //             'user_id' => auth()->id(),
    //             'action' => 'Printed document: ' . $document->name
    //         ]);
    //         return response()->file(public_path($document->file_path), [
    //             'Content-Disposition' => 'attachment; filename="' . $document->name . '.pdf"'
    //         ]);
    //     } catch (\Exception $e) {
    //         return General::apiFailureResponse('Document not found', 404);
    //     }
    // }

    public function index()
    {
        Log::info("Index method called by User ID: " . auth()->id());
        try {
            $documents = Document::query()
                ->select('id', 'name', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            // Add status to each document
            foreach ($documents as $document) {
                $document->status = 'success'; // Added status field to each document
            }

            $response = [
                'documents' => $documents
            ];

            return General::apiSuccessResponse('Documents retrieved successfully', 200, $response);
        } catch (\Exception $e) {
            return General::apiFailureResponse('Error retrieving documents', 500);
        }
    }
}

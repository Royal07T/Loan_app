<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    /**
     * Upload a document for a loan or user KYC.
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:50',
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'loan_id' => 'nullable|exists:loans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $file = $request->file('document');
        $path = $file->store('documents/' . $user->id, 'public');

        $document = Document::create([
            'user_id' => $user->id,
            'loan_id' => $request->loan_id,
            'type' => $request->type,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'data' => $document,
            'message' => 'Document uploaded successfully'
        ]);
    }

    /**
     * List user's uploaded documents.
     */
    public function myDocuments()
    {
        $documents = Auth::user()->documents()->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $documents,
            'message' => 'Documents retrieved successfully'
        ]);
    }

    /**
     * Admin: List all documents for review.
     */
    public function adminList(Request $request)
    {
        $query = Document::query();
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        $documents = $query->orderBy('created_at', 'desc')->paginate(20);
        return response()->json([
            'success' => true,
            'data' => $documents,
            'message' => 'All documents retrieved successfully'
        ]);
    }

    /**
     * Admin: Approve or reject a document.
     */
    public function review(Request $request, Document $document)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'review_notes' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        $document->update([
            'status' => $request->status,
            'review_notes' => $request->review_notes,
        ]);
        return response()->json([
            'success' => true,
            'data' => $document,
            'message' => 'Document review updated'
        ]);
    }

    /**
     * Download a document (admin or owner).
     */
    public function download(Document $document)
    {
        $user = Auth::user();
        if ($user->id !== $document->user_id && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }
        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }
}

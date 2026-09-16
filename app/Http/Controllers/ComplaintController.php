<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ComplaintController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ]);

        $attachment = $request->file('attachment');
        $attachmentPath = $attachment ? $attachment->store('complaints', 'public') : null;
        $complaint = Complaint::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'category' => $validated['category'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachment?->getClientOriginalName(),
        ]);

        $to = env('COMPLAINTS_TO_ADDRESS', config('mail.from.address'));
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        try {
            Mail::raw(
                "Complaint submitted via SLIA website\n\n"
                . "Name: {$validated['name']}\n"
                . "Email: " . ($validated['email'] ?: 'Not provided') . "\n"
                . "Category: {$validated['category']}\n"
                . "Subject: {$validated['subject']}\n\n"
                . "Message:\n{$validated['message']}\n",
                function ($message) use ($validated, $to, $fromAddress, $fromName, $attachment) {
                    $message->to($to)
                        ->cc([
                            'honysecretary@architects.lk',
                            'secretariat@architects.lk',
                        ])
                        ->subject('SLIA Complaint: ' . $validated['subject'])
                        ->from($fromAddress, $fromName)
                        ->replyTo($validated['email'] ?: $fromAddress, $validated['name']);

                    if ($attachment) {
                        $message->attach($attachment->getRealPath(), [
                            'as' => $attachment->getClientOriginalName(),
                            'mime' => $attachment->getMimeType(),
                        ]);
                    }
                }
            );
        } catch (\Throwable $exception) {
            // Keep the submission available in admin even when mail transport is unavailable.
            try {
                Log::error('Complaint notification could not be sent.', [
                    'complaint_id' => $complaint->id,
                    'error' => $exception->getMessage(),
                ]);
            } catch (\Throwable $loggingException) {
                error_log('Complaint notification could not be sent: ' . $exception->getMessage());
            }
        }

        return response()->json([
            'message' => 'Complaint sent successfully.',
            'complaint' => $complaint,
        ]);
    }

    public function index()
    {
        return response()->json(Complaint::latest()->get());
    }

    public function update(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:new,in_progress,resolved,closed'],
        ]);

        $complaint->update($validated);
        return response()->json($complaint->fresh());
    }

    public function download(Complaint $complaint)
    {
        abort_unless($complaint->attachment_path && Storage::disk('public')->exists($complaint->attachment_path), 404);

        return Storage::disk('public')->download(
            $complaint->attachment_path,
            $complaint->attachment_name ?: basename($complaint->attachment_path)
        );
    }
}

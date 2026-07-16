<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ComplaintController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $to = env('COMPLAINTS_TO_ADDRESS', config('mail.from.address'));
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        Mail::raw(
            "Complaint submitted via SLIA website\n\n"
            . "Name: {$validated['name']}\n"
            . "Email: " . ($validated['email'] ?: 'Not provided') . "\n"
            . "Subject: {$validated['subject']}\n\n"
            . "Message:\n{$validated['message']}\n",
            function ($message) use ($validated, $to, $fromAddress, $fromName) {
                $message->to($to)
                    ->cc('sliageneralsec@gmail.com')
                    ->subject('SLIA Complaint: ' . $validated['subject'])
                    ->from($fromAddress, $fromName)
                    ->replyTo($validated['email'] ?: $fromAddress, $validated['name']);
            }
        );

        return response()->json([
            'message' => 'Complaint sent successfully.',
        ]);
    }
}

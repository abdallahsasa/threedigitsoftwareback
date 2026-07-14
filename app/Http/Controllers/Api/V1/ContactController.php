<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'phone' => 'nullable|string|max:255',
        ]);

        $contactMessage = ContactMessage::create($validated);

        // TODO: Send Email Notification to Admin

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $contactMessage
        ], 201);
    }
}

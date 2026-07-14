<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class ProjectInquiryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'project_type' => 'nullable|string|max:255',
            'project_description' => 'nullable|string',
            'existing_website' => 'nullable|string|max:255',
            'required_features' => 'nullable|string',
            'preferred_launch_date' => 'nullable|string|max:255',
            'budget' => 'nullable|string|max:255',
            'privacy_consent' => 'required|boolean',
        ]);

        // Capture tracking metadata
        $validated['user_agent'] = $request->userAgent();
        $validated['ip_address'] = $request->ip();

        $lead = Lead::create($validated);

        // TODO: Handle attachments if provided
        // TODO: Send Email Notification to Admin & Confirmation to Customer

        return response()->json([
            'message' => 'Project inquiry submitted successfully',
            'data' => $lead
        ], 201);
    }
}

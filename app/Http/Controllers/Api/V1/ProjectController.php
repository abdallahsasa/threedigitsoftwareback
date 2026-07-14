<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $query = Project::with('category')->where('status', 'published');

        if ($request->query('featured') === 'true') {
            $query->where('is_featured', true);
        }

        $projects = $query->orderBy('order', 'asc')->get();

        $data = $projects->map(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
                'category' => $project->category ? $project->category->name : null,
                'overview' => $project->overview,
                'main_image' => $project->main_image ? (str_starts_with($project->main_image, 'http') ? $project->main_image : url('uploads/' . $project->main_image)) : null,
                'live_url' => $project->live_url,
                'is_featured' => (bool) $project->is_featured,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function show(Request $request, $slug)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $project = Project::with('category')
            ->where('status', 'published')
            ->where('slug->en', $slug)
            ->orWhere('slug->ar', $slug)
            ->firstOrFail();

        $data = [
            'id' => $project->id,
            'name' => $project->name,
            'slug' => $project->slug,
            'category' => $project->category ? $project->category->name : null,
            'overview' => $project->overview,
            'business_challenge' => $project->business_challenge,
            'proposed_solution' => $project->proposed_solution,
            'engineering_challenges' => $project->engineering_challenges,
            'results' => $project->results,
            'main_image' => $project->main_image ? (str_starts_with($project->main_image, 'http') ? $project->main_image : url('uploads/' . $project->main_image)) : null,
            'desktop_mockup' => $project->desktop_mockup ? (str_starts_with($project->desktop_mockup, 'http') ? $project->desktop_mockup : url('uploads/' . $project->desktop_mockup)) : null,
            'live_url' => $project->live_url,
            'client_name' => $project->client_name,
            'launch_year' => $project->launch_year,
            'is_featured' => (bool) $project->is_featured,
        ];

        return response()->json(['data' => $data]);
    }
}

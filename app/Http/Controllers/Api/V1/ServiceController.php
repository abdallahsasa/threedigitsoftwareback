<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Service;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $services = Service::where('is_published', true)
            ->orderBy('order', 'asc')
            ->get();

        $data = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'overview' => $service->overview,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function show(Request $request, $slug)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $service = Service::where('is_published', true)
            ->where('slug->en', $slug)
            ->orWhere('slug->ar', $slug)
            ->firstOrFail();

        $data = [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug,
            'overview' => $service->overview,
            'problems_solved' => $service->problems_solved,
            'capabilities' => $service->capabilities,
            'process' => $service->process,
        ];

        return response()->json(['data' => $data]);
    }
}

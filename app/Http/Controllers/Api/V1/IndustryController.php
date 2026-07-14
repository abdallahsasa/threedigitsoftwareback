<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Industry;

class IndustryController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');
        app()->setLocale($locale);

        $industries = Industry::where('is_active', true)
            ->orderBy('order', 'asc')
            ->get();

        $data = $industries->map(function ($industry) {
            return [
                'id' => $industry->id,
                'name' => $industry->name,
                'slug' => $industry->slug,
            ];
        });

        return response()->json(['data' => $data]);
    }
}

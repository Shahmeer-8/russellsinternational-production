<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LanguageProgram;
use Illuminate\Http\Request;

class LanguageProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = LanguageProgram::active()->with('section');

        if ($request->filled('section')) {
            $slug = $request->string('section')->toString();

            $query->whereHas('section', fn ($section) => $section->where('slug', $slug));
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function show(int $id)
    {
        return response()->json([
            'success' => true,
            'data' => LanguageProgram::active()->findOrFail($id),
        ]);
    }
}

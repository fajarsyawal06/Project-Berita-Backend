<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeatureToggle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class FeatureToggleController extends Controller
{
    public function index()
    {
        $features = FeatureToggle::latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $features
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|unique:feature_toggles,slug',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'target_role' => 'nullable|string',
            'activation_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
        }

        $feature = FeatureToggle::create($request->all());
        $this->clearCaches();

        return response()->json([
            'status' => 'success',
            'message' => 'Feature toggle created successfully',
            'data' => $feature
        ]);
    }

    public function show(string $id)
    {
        $feature = FeatureToggle::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $feature
        ]);
    }

    public function update(Request $request, string $id)
    {
        $feature = FeatureToggle::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'slug' => 'string|unique:feature_toggles,slug,'.$feature->id,
            'name' => 'string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'target_role' => 'nullable|string',
            'activation_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
        }

        $feature->update($request->all());
        $this->clearCaches();

        return response()->json([
            'status' => 'success',
            'message' => 'Feature toggle updated successfully',
            'data' => $feature
        ]);
    }

    public function destroy(string $id)
    {
        $feature = FeatureToggle::findOrFail($id);
        $feature->delete();
        $this->clearCaches();

        return response()->json([
            'status' => 'success',
            'message' => 'Feature toggle deleted successfully'
        ]);
    }

    private function clearCaches()
    {
        // Actually we would clear cache tags, but file driver doesn't support tags.
        // Or we just rely on the 30 sec expiration. But if we want instant toggle:
        // We can just call Cache::flush() but it's dangerous. Let's just let it expire in 30s.
        // The acceptance criteria explicitly states "within < 30 seconds" so we don't need instant sync.
    }
}

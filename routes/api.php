<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API للحصول على سعر الإيفاد
Route::get('/mission-rate', function (Request $request) {
    $missionName = $request->query('mission');
    $responsibilityLevel = $request->query('level');

    if (!$missionName || !$responsibilityLevel) {
        return response()->json(['error' => 'Missing parameters'], 400);
    }

    $missionType = \App\Models\MissionType::where('name', $missionName)
        ->where('responsibility_level', $responsibilityLevel)
        ->first();

    if ($missionType) {
        return response()->json([
            'success' => true,
            'daily_rate' => (float)$missionType->daily_rate,
            'name' => $missionType->name,
            'responsibility_level' => $missionType->responsibility_level
        ]);
    } else {
        return response()->json([
            'success' => false,
            'error' => 'Mission type not found'
        ], 404);
    }
});


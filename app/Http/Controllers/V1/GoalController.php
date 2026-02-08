<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GoalResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $goals = $request->user()->goals()
            ->orderByPivot('is_completed', 'desc')
            ->get();

        return $this->successResponse('Goals retrieved successfully', [
            'goals' => GoalResource::collection($goals),
        ]);
    }
}

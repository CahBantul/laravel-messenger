<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query');
        $users = User::query()
            ->where('name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->get();

        return response()->json(['users' => $users]);
    }
}

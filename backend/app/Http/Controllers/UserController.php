<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nickname' => 'required|string|max:191',
        ]);

        $nickname = trim($request->nickname);

        $user = User::where('nickname', $nickname)->first();

        if ($user) {
            return response()->json([
                'exists' => true,
                'user' => $user
            ]);
        }

        $user = User::create([
            'nickname' => $nickname
        ]);

        return response()->json([
            'exists' => false,
            'user' => $user
        ]);
    }
}

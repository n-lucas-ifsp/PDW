<?php 

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function show(Request $request)
    {
        $userId = Auth::user()->id;

        $user = User::findOrFail($userId);

        return response()->json(['user' => $user], 200);
    }

    public function resetPassword(Request $request)
    {
        $userId = $request->user()->id;

        $this->validate($request, [
            'new_password' => 'required|confirmed|string|min:8|max:255',
        ]);

        $user = User::findOrFail($userId);
        $user->password = app('hash')->make($request->input('new_password'));
        $user->save();

        return response()->json(['message' => 'Password reset successfully.'], 200);
    }
}

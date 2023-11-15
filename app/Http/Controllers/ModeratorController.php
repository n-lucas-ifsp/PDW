<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;

class ModeratorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function userlist()
    {
        $user = Auth::user();

        $users = [];

        if ($user->sys_level == 1) {
            $users = User::where('sys_level', '<=', config('enums.USER_LEVEL.MODERATOR'))->get();
        } else {
            $users = User::all();
        }

        return response()->json(['users' => $users], 200);
    }

    public function activate($id)
    {
        $user = User::findOrFail($id);

        if ($user->sys_level >= config('enums.USER_LEVEL.MODERATOR')) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        if ($user->active) {
            return response()->json(['error' => 'User is already active.'], 400);
        }

        $user->active = true;
        $user->save();

        $userProducts = $user->products()->count();
        $user->products()->update(['active' => true]);

        return response()->json(['message' => 'User activated.', 'productsAffected' => $userProducts], 200);
    }

    public function remove($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->sys_level >= config('enums.USER_LEVEL.MODERATOR')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $relatedProducts = Product::where('id_seller', $user->id)->get();
        $relatedTransactions = Transaction::where('id_buyer', $user->id)->get();

        if ($relatedProducts->isNotEmpty() || $relatedTransactions->isNotEmpty()) {
            $user->active = false;
            $user->save();

            foreach ($relatedProducts as $product) {
                $product->active = false;
                $product->save();
            }

            return response()->json(['message' => 'User deactivated, related products also deactivated.'], 200);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.'], 200);
    }

    public function showUserResume($id)
    {
        $user = User::findOrFail($id);

        if ($user->sys_level >= config('enums.USER_LEVEL.MODERATOR')) {
            return response()->json(['error' => 'Valid only to commom users.'], 400);
        }

        $transactionsOfSell = $user->transactionsOfSell()->with('product')->get();
        $transactionsOfBuy = $user->transactionsOfBuy()->with('product')->get();

        return response()->json([
            'user' => $user,
            'transactions_of_sell' => $transactionsOfSell,
            'transactions_of_buy' => $transactionsOfBuy
        ], 200);
    }
}
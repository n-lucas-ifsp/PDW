<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function showProductsResume(Request $request)
    {
        $this->validate($request, [
            'date_from' => 'required|date_format:Y-m-d',
            'date_until' => 'required|date_format:Y-m-d'
        ]);

        $dateFrom = Carbon::createFromFormat('Y-m-d', $request->input('date_from'));
        $dateUntil = Carbon::createFromFormat('Y-m-d', $request->input('date_until'));

        $totalMoneyMoved = Transaction::whereBetween('created_at', [$dateFrom, $dateUntil])
            ->sum('price');

        return response()->json([
            'total_money_moved' => $totalMoneyMoved
        ], 200);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Exception;

class WalletController extends Controller
{
    /**
     * Add money to the user's wallet.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addMoney(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:3|max:100|regex:/^\d+(\.\d{1,2})?$/',
            ]);

            $user = Auth::user();
            $user->wallet += $request->input('amount');
            $user->save();

            return response()->json([
                'message' => 'Money added successfully!',
                'wallet' => $user->wallet,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to add money to wallet.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buy cookies and deduct the cost from the user's wallet.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function buyCookie(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $user = Auth::user();
            $cookiePrice = 1.00;
            $quantity = $request->input('quantity');
            $totalCost = $cookiePrice * $quantity;

            if ($user->wallet < $totalCost) {
                return response()->json([
                    'message' => 'Insufficient funds in wallet.',
                ], 400);
            }

            $user->wallet -= $totalCost;
            $user->save();

            return response()->json([
                'message' => 'Cookie(s) purchased successfully!',
                'wallet' => $user->wallet,
                'cookies_bought' => $quantity,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to purchase cookies.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

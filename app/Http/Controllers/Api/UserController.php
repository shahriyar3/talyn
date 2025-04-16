<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    /**
     * UserController constructor.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get the authenticated user's information",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User information",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Shahriyar"),
     *             @OA\Property(property="email", type="string", example="shahriyar@email.com"),
     *             @OA\Property(property="gold_balance", type="number", format="float", example=8.517),
     *             @OA\Property(property="cash_balance", type="integer", example=100000000),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }

    /**
     * @OA\Get(
     *     path="/api/user/balance",
     *     summary="Get the authenticated user's balance",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User balance",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="gold_balance", type="number", format="float", example=8.517),
     *             @OA\Property(property="gold_balance_formatted", type="string", example="8.517 گرم"),
     *             @OA\Property(property="cash_balance", type="integer", example=100000000),
     *             @OA\Property(property="cash_balance_rial", type="string", example="100,000,000 ریال"),
     *             @OA\Property(property="cash_balance_toman", type="string", example="10,000,000 تومان"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();
        $balanceData = $this->userService->getUserBalance($user);

        return response()->json([
            'gold_balance' => $balanceData['gold_balance'],
            'gold_balance_formatted' => $balanceData['gold_balance'].' گرم',
            'cash_balance' => $balanceData['cash_balance'],
            'cash_balance_rial' => format_rial($balanceData['cash_balance']),
            'cash_balance_toman' => format_toman(rial_to_toman($balanceData['cash_balance'])),
        ]);
    }
}

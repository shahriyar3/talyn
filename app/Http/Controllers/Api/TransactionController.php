<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    /**
     * TransactionController constructor.
     */
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * @OA\Get(
     *     path="/api/transactions",
     *     summary="Get all transactions for the authenticated user",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of transactions",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_id", type="integer", example=1),
     *                 @OA\Property(property="buyer_id", type="integer", example=1),
     *                 @OA\Property(property="seller_id", type="integer", example=2),
     *                 @OA\Property(property="amount", type="number", format="float", example=2),
     *                 @OA\Property(property="price", type="integer", example=25000000),
     *                 @OA\Property(property="commission", type="integer", example=500000),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z"),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $transactions = $this->transactionService->getUserTransactions($request->user());

        return response()->json(TransactionResource::collection($transactions));
    }
}

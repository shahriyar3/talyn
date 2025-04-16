<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __construct(protected readonly OrderService $orderService) {}

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get all orders for the authenticated user",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="type", type="string", example="buy"),
     *                 @OA\Property(property="price", type="integer", example=10000000),
     *                 @OA\Property(property="amount", type="number", format="float", example=2),
     *                 @OA\Property(property="remaining_amount", type="number", format="float", example=1.5),
     *                 @OA\Property(property="status", type="string", example="partial"),
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
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $this->orderService->getUserOrders($request->user());

        return OrderResource::collection($orders);
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a new order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"type", "price", "amount"},
     *
     *             @OA\Property(property="type", type="string", enum={"buy", "sell"}, example="buy"),
     *             @OA\Property(property="price", type="integer", example=10000000),
     *             @OA\Property(property="amount", type="number", format="float", example=2.5),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="type", type="string", example="buy"),
     *             @OA\Property(property="price", type="integer", example=10000000),
     *             @OA\Property(property="amount", type="number", format="float", example=2.5),
     *             @OA\Property(property="remaining_amount", type="number", format="float", example=2.5),
     *             @OA\Property(property="status", type="string", example="open"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (e.g., insufficient balance)",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *     )
     * )
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = $request->user();

            if ($data['type'] === OrderType::BUY->value) {
                $totalCost = $data['price'] * $data['amount'];
                if ($user->cash_balance < $totalCost) {
                    return response()->json([
                        'message' => 'Insufficient cash balance',
                    ], Response::HTTP_BAD_REQUEST);
                }

                $order = $this->orderService->createBuyOrder($user, $data);
            } else {
                if ($user->gold_balance < $data['amount']) {
                    return response()->json([
                        'message' => 'Insufficient gold balance',
                    ], Response::HTTP_BAD_REQUEST);
                }

                $order = $this->orderService->createSellOrder($user, $data);
            }

            return response()->json($order, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'An error occurred while creating the order',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     summary="Cancel an order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order cancelled successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", example="cancelled"),
     *             @OA\Property(property="message", type="string", example="Order cancelled successfully"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (e.g., order already filled)",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access to order",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *     )
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $order = Order::query()->findOrFail($id);
            $user = $request->user();

            $cancelledOrder = $this->orderService->cancelOrder($order, $user);

            return response()->json([
                'id' => $cancelledOrder->id,
                'status' => $cancelledOrder->status->value,
                'message' => __('Order cancelled successfully'),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => __('Order not found'),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Unauthorized access to order') {
                return response()->json([
                    'message' => __('Unauthorized access to order'),
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'message' => __('Order cannot be cancelled'),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

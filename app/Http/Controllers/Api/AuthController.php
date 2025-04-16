<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Info(
 *     title="Talyn Gold Trading API",
 *     version="1.0.0",
 *     description="API for gold trading platform",
 *
 *     @OA\Contact(
 *         email="shahriyar.bayat@gmail.com",
 *         name="Shahriyar Bayat"
 *     )
 * )
 */
class AuthController extends Controller
{
    public function __construct(protected readonly UserService $userService) {}

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *
     *             @OA\Property(property="name", type="string", example="Shahriyar"),
     *             @OA\Property(property="email", type="string", format="email", example="shahriyar@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password"),
     *             @OA\Property(property="gold_balance", type="number", format="float", example=10),
     *             @OA\Property(property="cash_balance", type="integer", example=100000000),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Shahriyar"),
     *                 @OA\Property(property="email", type="string", example="shahriyar@email.com"),
     *                 @OA\Property(property="gold_balance", type="number", format="float", example=10),
     *                 @OA\Property(property="cash_balance", type="integer", example=100000000),
     *             ),
     *             @OA\Property(property="token", type="string", example="1|token"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->userService->register($data);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login a user",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="shahriyar@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Shahriyar"),
     *                 @OA\Property(property="email", type="string", example="shahriyar@email.com"),
     *             ),
     *             @OA\Property(property="token", type="string", example="1|token"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->userService->authenticate(
            $request->email,
            $request->password
        );

        if (! $user) {
            return response()->json([
                'message' => __('Invalid credentials'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout a user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Logged out successfully"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => __('Logged out successfully'),
        ]);
    }
}

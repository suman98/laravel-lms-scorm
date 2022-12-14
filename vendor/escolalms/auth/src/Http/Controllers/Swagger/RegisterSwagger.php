<?php

namespace EscolaLms\Auth\Http\Controllers\Swagger;

use EscolaLms\Auth\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;

interface RegisterSwagger
{
    /**
     * @OA\Post(
     *      path="/api/auth/register",
     *      description="Register new user",
     *      tags={"Auth"},
     *      @OA\Parameter(
     *          name="first_name",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="last_name",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="password_confirmation",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="return_url",
     *          required=true,
     *          in="query",
     *          example="https://escolalms.com/email/verify",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      )
     *   )
     */
    public function register(RegisterRequest $request): JsonResponse;
}

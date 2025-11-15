<?php

/**
 * This is an example of what the make:swagger command generates
 * when scanning Laravel routes.
 * 
 * Command used:
 * php artisan make:swagger
 * 
 * This file works WITHOUT any external dependencies.
 * The @OA\ annotations are just PHP comments.
 * 
 * Routes scanned:
 * Route::apiResource('products', ProductController::class);
 */

namespace App\Docs;

/**
 * @OA\Tag(name="Product")
 *
 * Note: This file contains OpenAPI annotations that work WITHOUT any external dependencies.
 * 
 * The @OA\ annotations here are processed by:
 * - zircote/swagger-php (https://github.com/zircote/swagger-php) - optional
 * - darkaonline/l5-swagger (wrapper for swagger-php) - optional
 *
 * You can use this file standalone OR integrate it with optional packages.
 */
class ProductDoc
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="List Product",
     *     tags={"Product"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Product Name"),
     *                 @OA\Property(property="price", type="number", format="float", example=99.99),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Resource not found.")
     *         )
     *     )
     * )
     */
    public function get_api_products(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create Product",
     *     tags={"Product"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name","price"},
     *             @OA\Property(property="name", type="string", example="Product Name"),
     *             @OA\Property(property="price", type="number", format="float", example=99.99),
     *             example={"name":"Product Name","price":99.99}
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Product Name"),
     *             @OA\Property(property="price", type="number", format="float", example=99.99),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T10:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T10:00:00Z"),
     *             example={"id":1,"name":"Product Name","price":99.99,"created_at":"2024-01-01T10:00:00Z","updated_at":"2024-01-01T10:00:00Z"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function post_api_products(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/api/products/{product}",
     *     summary="Show Product",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Product Name"),
     *             @OA\Property(property="price", type="number", format="float", example=99.99),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T10:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T10:00:00Z"),
     *             example={"id":1,"name":"Product Name","price":99.99,"created_at":"2024-01-01T10:00:00Z","updated_at":"2024-01-01T10:00:00Z"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Resource not found.")
     *         )
     *     )
     * )
     */
    public function get_api_products_product(): void
    {
    }

    /**
     * @OA\Put(
     *     path="/api/products/{product}",
     *     summary="Update Product",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Updated Name"),
     *             @OA\Property(property="price", type="number", format="float", example=199.99),
     *             example={"name":"Updated Name","price":199.99}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Updated Name"),
     *             @OA\Property(property="price", type="number", format="float", example=199.99),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T10:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T10:00:00Z"),
     *             example={"id":1,"name":"Updated Name","price":199.99,"created_at":"2024-01-01T10:00:00Z","updated_at":"2024-01-01T10:00:00Z"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Resource not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function put_api_products_product(): void
    {
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{product}",
     *     summary="Delete Product",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Resource not found.")
     *         )
     *     )
     * )
     */
    public function delete_api_products_product(): void
    {
    }
}

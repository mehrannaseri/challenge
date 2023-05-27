<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private ProductService $productService;

    /**
     * @param ProductService $productService
     */
    public function __construct(ProductService $productService)
    {

        $this->productService = $productService;
    }

    public function index(SearchProductRequest $request) :JsonResponse
    {
        $response = $this->productService->search($request->validated());

        return $this->response(ProductResource::collection($response));
    }
}

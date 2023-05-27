<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    private IntegrationService $integrationService;

    public $products;

    /**
     * @param IntegrationService $integrationService
     */
    public function __construct(IntegrationService $integrationService)
    {

        $this->integrationService = $integrationService;
    }

    public function search(array $data)
    {
        $this->getLocalProducts($data);

        $otherProducts = $this->integrationService->getList($data);

        $this->products = $this->products->merge($otherProducts);

        return $this->products;
    }

    public function getLocalProducts($data): void
    {
        $this->products = collect(Product::whereHas('availabilities', function($query) use ($data){
            $query->where('start_time', '>=', $data['startDate'])
                ->where('end_time', '<=', $data['endDate']);
        })
            ->withMin('availabilities', 'price')->get());
    }
}

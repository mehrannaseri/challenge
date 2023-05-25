<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function search(array $data)
    {
        $products = Product::whereHas('availabilities', function($query) use ($data){
            $query->where('start_time', '>=', $data['startDate']);
            $query->where('end_time', '<=', $data['endDate']);
        })
            ->withMin('availabilities', 'price')->get();

        return $products;
    }
}

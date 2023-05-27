<?php

namespace App\Services\Integration;

use App\Abstracts\IntegrationAbstract;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class HeavenlyTours extends IntegrationAbstract
{

    public $products;

    /**
     * @throws Exception
     */
    public function getData() :Collection
    {
        $data = $this->getProducts()
            ->filterUnAvailableProducts()
            ->getPrice()
            ->getThumbnail()
            ->response();

        return $data;
    }

    public function response() : Collection
    {
        $products = Product::hydrate($this->products->toArray());
        $products->each(function($product){
            $product->setVisible(['name', 'price', 'thumbnail']);
            $product->name = $product->title;
            $product->availabilities_min_price = $product->price;
        });

        return $products;
    }

    /**
     * @throws Exception
     */
    private function getProducts(): static
    {
        $this->products = collect($this->sendRequest('/tours'));

        return $this;
    }

    /**
     * @throws Exception
     */
    private function filterUnAvailableProducts(): static
    {
        $uris = $this->generateAvailabilityUri(Arr::pluck($this->products, 'id'));

        $responses = $this->sendBulkRequest($uris);

        for ($i = 0; $i < count($this->products); $i++){
            $availableResponse = $responses[$i]->json();
            if(!$availableResponse['available'])
                array_splice($this->products, $i, 1);
        }

        return $this;

    }

    private function generateAvailabilityUri(array $productsIdArray): array
    {
        $uris = [];
        foreach ($productsIdArray as $value){
            $uris[] = "/tours/{$value}/availability?travelDate={$this->data['startDate']}";
        }

        return $uris;
    }

    private function getPrice(): static
    {
        $response = collect($this->sendRequest("/tour-prices?travelDate={$this->data['startDate']}"));

        $response = $response->groupBy('tourId')->map(function ($row) {
            return $row->min('price');
        });

        $products = $this->products->map(function($product) use ($response){
           $product['price'] = $response[$product['id']];
           return $product;
        });

        //remove duplicate id
        $this->products = $products->unique('id');

        return $this;
    }

    /**
     * @throws Exception
     */
    private function getThumbnail(): static
    {
        $uris = $this->generateProductDetailUri(Arr::pluck($this->products, 'id'));

        $responses = $this->sendBulkRequest($uris);

        $i = 0;
        $products = $this->products->map(function($product) use ($responses, &$i){
            $productDetail = $responses[$i]->json();
            $thumbnail = Arr::where($productDetail['photos'], function($value, $key){
                return $value['type'] == 'thumbnail';
            });

            $product['thumbnail'] = $thumbnail[0]['url'];
            $i++;
            return $product;
        });

        $this->products = $products;
        return $this;
    }

    private function generateProductDetailUri(array $productsIdArray) :array
    {
        $uris = [];
        foreach ($productsIdArray as $value){
            $uris[] = "/tours/{$value}";
        }

        return $uris;
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /** @var Product $this */
        return [
            'title' => $this->name,
            'minimumPrice' => $this->availabilities_min_price." ".config('setting.defaultCurrency'),
            'thumbnail' => $this->thumbnail
        ];
    }
}

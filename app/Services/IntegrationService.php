<?php

namespace App\Services;

use Illuminate\Support\Collection;
use ReflectionClass;

class IntegrationService
{
    private $providers;

    public $providerProducts;

    public function __construct()
    {
        $this->providerProducts = new Collection();
    }

    public function getList($data)
    {
        $this->getProviders();

        foreach ($this->providers as $provider){
            $provider = $this->runProvider($provider, $data);

            $response = $provider->getdata();
            $this->providerProducts = $this->providerProducts->merge($response);
        }

        return $this->providerProducts;
    }

    private function getProviders()
    {
        $this->providers = config('services.providers');
    }

    private function runProvider($provider, $data)
    {
        $service = "App\Services\Integration\\".$provider['name'];
        return new $service($provider['base_url'], $data);
    }
}

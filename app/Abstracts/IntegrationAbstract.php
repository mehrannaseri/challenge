<?php

namespace App\Abstracts;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

abstract class IntegrationAbstract
{
    public $url;
    public $data;

    /**
     * @param $url
     * @param $data
     */
    public function __construct($url, $data)
    {
        $this->data = $data;
        $this->url = $url;
    }

    abstract public function getData() :Collection;

    abstract public function response() :Collection;

    /**
     * @param $address
     * @return mixed|void
     * @throws Exception
     */
    protected function sendRequest($uri)
    {
        try{
            $request = Http::get($this->url.$uri);
            if($request->ok())
                return $request->json();
        }
        catch (Exception $e){
            throw $e;
        }
    }

    protected function sendBulkRequest(Array $uris)
    {
        try{
            return Http::pool(function(Pool $pool) use ($uris){
                foreach ($uris as $uri){
                    $pool->get($this->url.$uri);
                }
            });
        }
        catch (Exception $e){
            throw $e;
        }

    }
}

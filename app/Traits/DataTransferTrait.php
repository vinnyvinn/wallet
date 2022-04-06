<?php

namespace App\Traits;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\RequestException;

trait DataTransferTrait
{

    protected $client;

    public function __construct()
    {
        $this->client = new Client(['http_errors' => false,]);
    }

    public function sendDataPost($url, $data)
    {
        Log::notice(json_encode($data));
        $this->client = new Client(['http_errors' => true,]);
        $request = new Request('POST', $url, ['Content-Type' => 'application/x-www-form-urlencoded'], http_build_query($data, null, '&'));
        $promise = $this->client->sendAsync($request)->then(
            function (ResponseInterface $response) {
                Log::notice(serialize($response->getBody()));
            },
            function (RequestException $e) {
                if ($e->hasResponse()) {
                    Log::error(Psr7\str($e->getResponse()));
                } else {
                    Log::error($e->getMessage());
                }
            }
        );
        $promise->wait();
    }

    public function sendDataGet($url, $data)
    {
        try {
            $request = new Request('GET', $url, ["form_params" => $data]);
            $promise = $this->client->sendAsync($request)->then(function ($response) {
                echo 'I completed! ' . $response->getBody();
            });
            $promise->wait();
        } catch (RequestException $e) {
            Log::info($e->getRequest());
            if ($e->hasResponse()) {
                Log::error($e->getResponse());
            }
        }
    }

    public function guzzlePostRequest($url, $data)
    {
        try {
            $this->client = new Client(['http_errors' => true,]);
            $request = new Request('POST', $url, ['Content-Type' => 'application/x-www-form-urlencoded'],  http_build_query($data, null, '&'));
            $response = $this->client->send($request, ['timeout' => 40]);
            return $response->getBody();
        } catch (RequestException $e) {
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
            } else {
                Log::error($e->getMessage());
            }
        } catch (ClientException $e) {
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
            } else {
                Log::error($e->getMessage());
            }
        } catch (ServerException $e) {
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
            } else {
                Log::error($e->getMessage());
            }
        }
    }

    public function serviceGetRequest($url, $token = '')
    {
        try {
            $headers = [
                'Authorization' => $token,
                'Accept' => 'application/json',
            ];
            $this->client = new Client(['http_errors' => true,]);
            $request = new Request('GET', $url, $headers);

            $auth_header = $request->getHeader('Authorization');
            if (!empty($auth_header[0])) {
                $response = $this->client->send($request, ['timeout' => 40]);
                // Log::info(serialize($response->getBody()->getContents()));      
                return $response->getBody();
            } else {
                throw new \Exception('Authorization header missing');
            }
        } catch (RequestException $e) {
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
                if ($e->getResponse()->getStatusCode() == 401) {
                    throw new \Exception("Credentials authentication failed.");
                } else {
                    throw new \Exception($e->getMessage());
                }
            } else {
                Log::error($e->getMessage());
                throw new \Exception($e->getMessage());
            }
        } catch (ClientException $e) {
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
                throw new \Exception($e->getMessage());
            } else {
                Log::error($e->getMessage());
                throw new \Exception($e->getMessage());
            }
        } catch (ServerException $e) {
            Log::error(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                Log::error(Psr7\str($e->getResponse()));
                throw new \Exception($e->getMessage());
            } else {
                Log::error($e->getMessage());
                throw new \Exception($e->getMessage());
            }
        }
    }
}

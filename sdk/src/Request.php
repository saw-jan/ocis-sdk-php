<?php
namespace Sawjan\OcisSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Request {
    private $client;
    private $options;

    function __construct($options){
        $this->options = [
            "base_uri" => $options['ocis_url'],
            // insecure for development environment
            'verify' => false,
        ];
        $this->client = new Client($this->options);
    }

    function send($method, $path, $headers = [], $body = null){
        try{
            return $this->client->request($method, $path, [
                'headers' => $headers,
                'body' => $body
            ]);
        } catch(ClientException $e){
            return $e->getResponse();
        }
    }

    function createClientWithAuth($token){
        $options = array_merge($this->options, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        $this->client = new Client(
            $options
        );
    }
}

?>
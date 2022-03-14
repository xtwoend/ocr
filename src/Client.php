<?php

namespace Growinc\OCR;

use GuzzleHttp\Psr7\Utils;

class Client
{
    protected $host = 'https://api.idcentral.io';
    protected $headers = [];
    protected $options = [];

    public function __construct() 
    {
        $this->key = config('ocr.api-key');
        $this->headers = [
            'api-key' => $this->key,
            'isConsentGranted' => true
        ];
    }

    public function ocr($idCard, $type = 'KTP')
    {
        $types = ['KTP' => 'NID', 'PASPOR' => 'PSPT', 'SIM' => 'DL'];

        if(! in_array($type, $types))
            return;

        $this->options['multipart'] = [
            [
                'name'     => 'id_document_image',
                'contents' => Utils::tryFopen($idCard, 'rb'),
                'filename' => basename($idCard)
            ]
        ];

        return $this->exec("/idc/onboarding/ocr/ID/id/{$types[$type]}");
    }

    public function faceMatch($idCard, $selfie)
    {
        $this->options['multipart'] = [
            [
                'name'     => 'id_document_image',
                'contents' => Utils::tryFopen($idCard, 'rb'),
                'filename' => basename($idCard)
            ],
            [
                'name'     => 'selfie_image',
                'contents' => Utils::tryFopen($selfie, 'rb'),
                'filename' => basename($selfie)
            ]
        ];

        return $this->exec("/idc/onboarding/face/match");
    }

    // error not found
    public function ktpVerify(array $data = [])
    {
        $this->options['json'] = $data;
        return $this->exec('/verification/ktp');
    }

    protected function exec($path, string $method = 'POST', array $headers = [])
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->host,
            'timeout' => 20.0
        ]);

        $this->options['headers'] = array_merge_recursive($this->headers, ...$headers); 

        $resp = $client->request($method, $path, $this->options);
        
        if($resp->getReasonPhrase() == 'OK') {
            $body = (string) $resp->getBody();
            $data = json_decode($body);
            if(! $data->error) {
                return $data->data;
            }
        }

        return false;
    }
}
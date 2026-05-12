<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class ShipStationService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => env('SHIPSTATION_API_BASE'),
            'auth' => [env('SHIPSTATION_API_KEY'), env('SHIPSTATION_API_SECRET')],
            'headers' => ['Accept' => 'application/json'],
        ]);

    }
    /**
     * Get the list of carriers connected to the account
     */
    public function getCarriers()
    {
        $res = $this->client->get('/carriers');

        $data = json_decode($res->getBody(), true);
        dd($data);

        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'No carriers configured in ShipStation account. Please add carriers in ShipStation settings.'
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }


    public function createOrder(array $orderPayload)
    {

        $res = $this->client->post('/orders/createorder', [
            'json' => $orderPayload
        ]);
        return json_decode($res->getBody(), true);
    }

    public function createLabelForOrder(int $orderId, array $labelPayload)
    {
        $payload = array_merge(['orderId' => $orderId], $labelPayload);

        //dd($payload);
        // Correct ShipStation endpoint
        $res = $this->client->post('/orders/createlabelfororder', [
            'json' => $payload
        ]);

        $data = json_decode($res->getBody(), true);

        if (!empty($data['labelData'])) {
            $pdf = base64_decode($data['labelData']);
            $fileName = 'label_' . $orderId . '.pdf';
            $path = 'public/shipstation/' . $fileName;
            Storage::put($path, $pdf);
            $data['saved_path'] = Storage::url($path);
        }

        return $data;
    }
}

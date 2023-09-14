<?php

namespace App\Http\Services;

use App\Http\Services\BaseService;
use App\Exceptions\DataEmptyException;

class RajaOngkirService extends BaseService
{
    private $api_key;

    public function __construct()
    {
        $this->api_key = env('RAJAONGKIR_API_KEY');
    }

    public function getProvince($locale, $id)
    {
        $id = $id ?? null;

        $params = [];
        if (!is_null($id)) {
            $params['id'] = $id;
        }

        $url = "https://api.rajaongkir.com/starter/province?" . http_build_query($params);
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: " . $this->api_key
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return response()->json([
                'message' => "cURL Error #:" . $err,
                'status' => 500,
            ], 500);
        }

        $data = json_decode($response, true);

        $collection = collect($data['rajaongkir']['results']);

        if($collection->isEmpty()) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'Province'], $locale));

        return response()->json([
            'data' => $collection,
        ]);
    }

    public function getCity($locale, $id, $province_id)
    {
        $id = $id ?? null;
        $province_id = $province_id ?? null;

        $params = [];
        if (!is_null($id)) {
            $params['id'] = $id;
        }
        if (!is_null($province_id)) {
            $params['province'] = $province_id;
        }

        $url = "https://api.rajaongkir.com/starter/city?" . http_build_query($params);
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: " . $this->api_key
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return response()->json([
                'message' => "cURL Error #:" . $err,
                'status' => 500,
            ], 500);
        }

        $data = json_decode($response, true);

        $collection = collect($data['rajaongkir']['results']);

        if($collection->isEmpty()) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'City'], $locale));

        return response()->json([
            'data' => $collection,
        ]);
    }

    public function getCost($locale, $request)
    {
        $request->validate([
            'origin_city' => 'required',
            'destination_city' => 'required',
            'weight' => 'required|integer',
            'courier' => 'required|in:jne,pos,tiki',
        ]);

        $data_request = $request->only([
            'origin_city',
            'destination_city',
            'weight',
            'courier',
        ]);

        $body = http_build_query([
            'origin' => $data_request['origin_city'],
            'destination' => $data_request['destination_city'],
            'weight' => $data_request['weight'],
            'courier' => $data_request['courier'],
        ]);

        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: " . $this->api_key
            ),
        ));
          
        $response = curl_exec($curl);
        $err = curl_error($curl);
          
        curl_close($curl);

        if ($err) {
            return response()->json([
                'message' => "cURL Error #:" . $err,
                'status' => 500,
            ], 500);
        }

        $data = json_decode($response, true);

        $collection = collect($data['rajaongkir']['results']);

        $costs = $collection[0]['costs'];

        if(empty($costs)) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'Cost'], $locale));

        return response()->json([
            'data' => $collection,
        ]);
    }
}
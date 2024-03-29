<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use App\Exceptions\DataEmptyException;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\CityRepository;
use App\Http\Repositories\ProvinceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Repositories\SubdistrictRepository;

class RajaOngkirService extends BaseService
{
    private $api_key, $province_repository, $city_repository, $subdistrict_repository, $origin;

    public function __construct(ProvinceRepository $province_repository, CityRepository $city_repository, SubdistrictRepository $subdistrict_repository)
    {
        $this->api_key = config('services.rajaongkir.key');
        $this->province_repository = $province_repository;
        $this->city_repository = $city_repository;
        $this->subdistrict_repository = $subdistrict_repository;
        $this->origin = config('setting.shipping.origin_id');
    }

    public function getProvince($locale, $id, $page, $per_page)
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

        if($err) throw new ApplicationException('cURL Error #: '. $err);

        $data = json_decode($response, true);

        if($data['rajaongkir']['status']['code'] == 400) throw new ApplicationException($data['rajaongkir']['status']['description']);


        $collection = collect($data['rajaongkir']['results']);

        if($collection->isEmpty()) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'Province'], $locale));

        if(is_multidimensional_array($collection->toArray())) {
            $response = response()->api(null, $this->format_json($collection, $page, $per_page, ['path' => config('app.url') . '/api/v1/id/rajaongkir/provinces'])['data']);
        } else {
            $response = response()->api(null, $collection);
        }

        return $response;
    }

    public function getCity($locale, $id, $province_id, $page, $per_page)
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

        if($err) throw new ApplicationException('cURL Error #: '. $err);

        $data = json_decode($response, true);

        if($data['rajaongkir']['status']['code'] == 400) throw new ApplicationException($data['rajaongkir']['status']['description']);


        $collection = collect($data['rajaongkir']['results']);

        if($collection->isEmpty()) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'City'], $locale));

        if(is_multidimensional_array($collection->toArray())) {
            $response = response()->api($this->format_json($collection, $page, $per_page, ['path' => config('app.url') . '/api/v1/id/rajaongkir/get-city'])['data']);
        } else {
            $response = response()->api(null, $collection);
        }

        return $response;
    }

    public function getCost($locale, $data)
    {
        $data_request = Arr::only($data, [
            'destination_city',
            'weight',
            'courier',
        ]);

        $this->validate($data_request, [
                'destination_city' => 'required',
                'weight' => 'required|integer',
                'courier' => 'required|in:jne,pos,tiki',
            ]
        );

        $body = http_build_query([
            'origin' => $this->origin,
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

        if($err) throw new ApplicationException('cURL Error #: '. $err);

        $data = json_decode($response, true);

        if($data['rajaongkir']['status']['code'] == 400) throw new ApplicationException($data['rajaongkir']['status']['description']);

        $collection = collect($data['rajaongkir']['results']);

        $costs = $collection[0]['costs'];

        if(empty($costs)) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'Cost'], $locale));

        return response()->api(null, $collection);
    }

    private function format_json($original_data, $page, $per_page, $options)
    {
        $data_collection = $this->paginate($original_data, $page, $per_page, $options);

        $transformed_data = $data_collection->map(function ($item) {
            return $item;
        });

        $data_array = $data_collection->toArray();

        $results = [
            'data' => $transformed_data->toArray(),
            'links' => [
                'first' => $data_array['first_page_url'],
                'last' => $data_array['last_page_url'],
                'prev' => $data_array['prev_page_url'],
                'next' => $data_array['next_page_url'],
            ],
            'meta' => [
                'current_page' => $data_array['current_page'],
                'from' => $data_array['from'],
                'last_page' => $data_array['last_page'],
                'links' => $data_array['links'],
                'path' => $data_array['path'],
                'per_page' => $data_array['per_page'],
                'to' => $data_array['to'],
                'total' => $data_array['total'],
            ],
        ];

        return $results;
    }

    private function paginate($data, $page = null, $per_page = 15, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $data = $data instanceof Collection ? $data : Collection::make($data);
        return new LengthAwarePaginator($data->forPage($page, $per_page), $data->count(), $per_page, $page, $options);
    }
    
}
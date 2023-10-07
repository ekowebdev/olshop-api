<?php

namespace App\Http\Services;

use App\Http\Services\BaseService;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use App\Exceptions\DataEmptyException;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Repositories\SubdistrictRepository;

class RajaOngkirService extends BaseService
{
    private $api_key, $subdistrict_repository;

    public function __construct(SubdistrictRepository $subdistrict_repository)
    {
        $this->api_key = env('RAJAONGKIR_API_KEY');
        $this->subdistrict_repository = $subdistrict_repository;
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

        if ($err) {
            return response()->json([
                'message' => "cURL Error #:" . $err,
                'status' => 500,
            ], 500);
        }

        $data = json_decode($response, true);

        if ($data['rajaongkir']['status']['code'] == 400) {
            return response()->json([
                'message' => $data['rajaongkir']['status']['description'],
                'status' => 400,
            ], 400);
        }

        $collection = collect($data['rajaongkir']['results']);

        if($collection->isEmpty()) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'Province'], $locale));

        if(is_multidimensional_array($collection->toArray())) {
            $response = response()->json($this->format_json($collection, $page, $per_page, ['path' => 'http://localhost:9000/api/v1/id/rajaongkir/get-province']));
        } else {
            $response = response()->json([
                'data' => $collection
            ]);
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

        if ($err) {
            return response()->json([
                'message' => "cURL Error #:" . $err,
                'status' => 500,
            ], 500);
        }

        $data = json_decode($response, true);

        if ($data['rajaongkir']['status']['code'] == 400) {
            return response()->json([
                'message' => $data['rajaongkir']['status']['description'],
                'status' => 400,
            ], 400);
        }

        $collection = collect($data['rajaongkir']['results']);

        if($collection->isEmpty()) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'City'], $locale));

        if(is_multidimensional_array($collection->toArray())) {
            $response = response()->json($this->format_json($collection, $page, $per_page, ['path' => 'http://localhost:9000/api/v1/id/rajaongkir/get-city']));
        } else {
            $response = response()->json([
                'data' => $collection
            ]);
        }

        return $response;
    }

    public function getSubdistrict($locale, $data)
    {
        $search = [
            'city_id' => 'city_id',
            'subdistrict_name' => 'subdistrict_name',
        ];

        $search_column = [
            'subdistrict_id' => 'subdistrict_id',
            'city_id' => 'city_id',
            'subdistrict_name' => 'subdistrict_name',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];
        
        return $this->subdistrict_repository->getIndexData($locale, $sortable_and_searchable_column);
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
            'origin' => $request['origin_city'],
            'destination' => $request['destination_city'],
            'weight' => $request['weight'],
            'courier' => $request['courier'],
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

        if ($data['rajaongkir']['status']['code'] == 400) {
            return response()->json([
                'message' => $data['rajaongkir']['status']['description'],
                'status' => 400,
            ], 400);
        }

        $collection = collect($data['rajaongkir']['results']);

        $costs = $collection[0]['costs'];

        if(empty($costs)) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'Cost'], $locale));

        return response()->json([
            'data' => $collection,
        ]);
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
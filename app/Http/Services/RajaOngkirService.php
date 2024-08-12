<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use App\Exceptions\SystemException;
use App\Exceptions\DataEmptyException;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\CityRepository;
use App\Http\Repositories\ProvinceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Repositories\SubdistrictRepository;

class RajaOngkirService extends BaseService
{
    private $apiKey, $origin, $provinceRepository, $cityRepository, $subdistrictRepository;

    public function __construct(ProvinceRepository $provinceRepository, CityRepository $cityRepository, SubdistrictRepository $subdistrictRepository)
    {
        $this->apiKey = config('services.rajaongkir.key');
        $this->origin = config('setting.shipping.origin_id');
        $this->provinceRepository = $provinceRepository;
        $this->cityRepository = $cityRepository;
        $this->subdistrictRepository = $subdistrictRepository;
    }

    public function getProvince($locale, $id, $page, $perPage)
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
                "key: " . $this->apiKey
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if($err) throw new SystemException('cURL Error #: '. $err);

        $data = json_decode($response, true);

        if($data['rajaongkir']['status']['code'] == 400) throw new ApplicationException($data['rajaongkir']['status']['description']);

        $collection = collect($data['rajaongkir']['results']);

        if($collection->isEmpty()) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'Province'], $locale));

        if(isMultidimensionalArray($collection->toArray())) {
            $response = response()->api(null, formatJson($collection, $page, $perPage, ['path' => config('app.url') . '/api/v1/' . $locale . '/rajaongkir/provinces'])['data']);
        } else {
            $response = response()->api(null, $collection);
        }

        return $response;
    }

    public function getCity($locale, $id, $provinceId, $page, $perPage)
    {
        $id = $id ?? null;
        $provinceId = $provinceId ?? null;

        $params = [];

        if (!is_null($id)) {
            $params['id'] = $id;
        }

        if (!is_null($provinceId)) {
            $params['province'] = $provinceId;
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
                "key: " . $this->apiKey
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if($err) throw new SystemException('cURL Error #: '. $err);

        $data = json_decode($response, true);

        if($data['rajaongkir']['status']['code'] == 400) throw new ApplicationException($data['rajaongkir']['status']['description']);

        $collection = collect($data['rajaongkir']['results']);

        if($collection->isEmpty()) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'City'], $locale));

        if(isMultidimensionalArray($collection->toArray())) {
            $response = response()->api(null, formatJson($collection, $page, $perPage, ['path' => config('app.url') . '/api/v1/' . $locale . '/rajaongkir/get-city'])['data']);
        } else {
            $response = response()->api(null, $collection);
        }

        return $response;
    }

    public function getCost($locale, $data)
    {
        $request = Arr::only($data, [
            'destination_city',
            'weight',
            'courier',
        ]);

        $this->validate($request, [
            'destination_city' => 'required',
            'weight' => 'required|integer',
            'courier' => 'required|in:jne,pos,tiki',
        ]);

        $body = http_build_query([
            'origin' => $this->origin,
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
                "key: " . $this->apiKey
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if($err) throw new SystemException('cURL Error #: '. $err);

        $data = json_decode($response, true);

        if($data['rajaongkir']['status']['code'] == 400) throw new ApplicationException($data['rajaongkir']['status']['description']);

        $collection = collect($data['rajaongkir']['results']);

        $costs = $collection[0]['costs'];

        if(empty($costs)) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'Cost'], $locale));

        return response()->api(null, $collection);
    }
}

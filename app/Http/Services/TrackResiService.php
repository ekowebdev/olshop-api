<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Exceptions\DataEmptyException;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\OrderRepository;

class TrackResiService extends BaseService
{
    private $apiKey, $repository;

    public function __construct(OrderRepository $repository)
    {
        $this->apiKey = config('services.binderbyte.key');
        $this->repository = $repository;
    }

    public function track($locale, $data)
    {
        $request = Arr::only($data, [
            'user_id',
            'resi',
            'courier',
        ]);

        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'resi' => 'required',
            'courier' => 'required|in:jne,jnt,pos,tiki,spx',
        ]);

        $check = $this->repository->getSingleDataByIdAndReceipt($request['user_id'], $request['resi']);

        if(!$check) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'Receipt'], $locale));

        $body = http_build_query([
            'api_key' => $this->api_key,
            'awb' => $request['resi'],
            'courier' => $request['courier'],
        ]);

        $url = 'https://api.binderbyte.com/v1/track?' . $body;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if($err) throw new ApplicationException('cURL Error #: '. $err);

        $data = json_decode($response, true);

        if($data['status'] == 400) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'Receipt'], $locale));

        $data = $data['data'];

        return response()->api(null, $data);
    }
}

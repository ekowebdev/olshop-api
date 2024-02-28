<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Exceptions\DataEmptyException;
use App\Http\Repositories\RedeemRepository;

class TrackResiService extends BaseService
{
    private $api_key, $repository;

    public function __construct(RedeemRepository $repository)
    {
        $this->api_key = config('services.binderbyte.key');
        $this->repository = $repository;
    }

    public function track($locale, $data)
    {
        $data_request = Arr::only($data, [
            'user_id',
            'resi',
            'courier',
        ]);

        $this->validate($data_request, [
            'user_id' => 'required|exists:users,id',
            'resi' => 'required',
            'courier' => 'required|in:jne,jnt,pos,tiki,spx',
        ]);

        $check = $this->repository->getDataByIdAndResi($data_request['user_id'], $data_request['resi']);

        if(!$check) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'Resi'], $locale));
        
        $body = http_build_query([
            'api_key' => $this->api_key,
            'awb' => $data_request['resi'],
            'courier' => $data_request['courier'],
        ]);

        $url = "https://api.binderbyte.com/v1/track?" . $body;

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

        if ($err) {
            return response()->json([
                'message' => "cURL Error #:" . $err,
                'status' => 500,
            ], 500);
        }

        $data = json_decode($response, true);

        if($data['status'] == 400) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'Resi'], $locale));

        $data = $data['data'];

        return response()->json(['data' => $data]);
    }
    
}
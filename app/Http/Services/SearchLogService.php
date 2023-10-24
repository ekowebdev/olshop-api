<?php

namespace App\Http\Services;

use App\Http\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\SearchLog;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException;
use Aws\DynamoDb\Exception\DynamoDbException;
use App\Http\Repositories\SearchLogRepository;

class SearchLogService extends BaseService
{
    private $model, $repository;

    public function __construct(SearchLog $model, SearchLogRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale)
    {
        return $this->repository->getIndexData($locale);
    }

    public function getSingleData($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function getDataByUser($locale, $id)
    {
        return $this->repository->getDataByUser($locale, $id);
    }

    public function store($locale, $data)
    {
        $data_request = Arr::only($data, [
            'user_id',
            'search_text',
        ]);

        $this->repository->validate($data_request, [
                'search_text' => [
                    'required',
                    'min:3',
                    'string',
                ],
            ]
        );

        try{
            DB::beginTransaction();
            $data = $this->model;
            $data->id = strval(Str::uuid());
            $data->user_id = intval(auth()->user()->id);
            $data->search_text = $data_request['search_text'];
            $data->save();
            DB::commit();
        } catch (DynamoDbException $e) {
            DB::rollback();
            throw new ValidationException(json_encode([$e->getMessage()]));
        }

        return $this->repository->getSingleData($locale, $data->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'user_id' => $check_data->user_id,
            'search_text' => $check_data->search_text,
        ], $data);

        $data_request = Arr::only($data, [
            'user_id',
            'min:3',
            'search_text',
        ]);

        $this->repository->validate($data_request, [
            'search_text' => [
                'string',
            ],
        ]);

        DB::beginTransaction();
        $data_request['user_id'] = intval(auth()->user()->id);
        $check_data->update($data_request);
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        DB::beginTransaction();
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
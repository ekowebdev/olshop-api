<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\SearchLog;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ApplicationException;
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
            $data->id = (string) Str::uuid();
            $data->user_id = (int) auth()->user()->id;
            $data->search_text = strtolower($data_request['search_text']);
            $data->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw new ApplicationException(json_encode([$e->getMessage()]));
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
            'search_text',
        ]);

        $this->repository->validate($data_request, [
            'search_text' => [
                'string',
                'min:3',
            ],
        ]);

        DB::beginTransaction();
        $data_request['user_id'] = (int) auth()->user()->id;
        $data_request['search_text'] = strtolower($data_request['search_text']);
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

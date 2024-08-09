<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\SearchLog;
use Illuminate\Support\Facades\DB;
use App\Exceptions\SystemException;
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

    public function index($locale)
    {
        return $this->repository->getAllData($locale);
    }

    public function show($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function showByUser($locale, $id)
    {
        return $this->repository->getDataByUser($locale, $id);
    }

    public function store($locale, $data)
    {
        $request = Arr::only($data, [
            'user_id',
            'search_text',
        ]);

        $this->repository->validate($request, [
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
            $data->search_text = strtolower($request['search_text']);
            $data->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw new SystemException(json_encode([$e->getMessage()]));
        }

        return $this->repository->getSingleData($locale, $data->id);
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'user_id' => $checkData->user_id,
            'search_text' => $checkData->search_text,
        ], $data);

        $request = Arr::only($data, [
            'user_id',
            'search_text',
        ]);

        $this->repository->validate($request, [
            'search_text' => [
                'string',
                'min:3',
            ],
        ]);

        DB::beginTransaction();
        $request['user_id'] = (int) auth()->user()->id;
        $request['search_text'] = strtolower($request['search_text']);
        $checkData->update($request);
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);
        DB::beginTransaction();
        $result = $checkData->delete();
        DB::commit();

        return $result;
    }
}

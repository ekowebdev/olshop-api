<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\SearchLog;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchLogRepository extends BaseRepository
{
    private $repository = 'Search Log';
    private $model;

	public function __construct(SearchLog $model)
	{
		$this->model = $model;
	}

    public function getAllData($locale)
    {
        $perPage = (int) Request::get('per_page', 10);
        $page = (int) Request::get('page', 1);

        $data = $this->model->query()->orderBy('created_at', 'desc')->get();

        if ($data->isEmpty()) {
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));
        }

        $result = new LengthAwarePaginator(
            $data->forPage($page, $perPage),
            $data->count(),
            $perPage,
            $page,
            ['path' => url('/api/v1/' . $locale . '/search-logs')]
        );

        if ($result->isEmpty()) {
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));
        }

        return $result;
    }

    public function getDataByUser($locale, $userId)
	{
        $userId = (int) $userId;
        $perPage = (int) Request::get('per_page', 10);
        $page = (int) Request::get('page', 1);

		$data = $this->model->query()->orderBy('created_at', 'desc')->where('user_id', $userId)->get();

        if ($data->isEmpty()) {
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));
        }

        $result = new LengthAwarePaginator(
            $data->forPage($page, $perPage),
            $data->count(),
            $perPage,
            $page,
            ['path' => url('/api/v1/' . $locale . '/search-logs')]
        );

        if ($result->isEmpty()) {
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));
        }

        return $result;
	}

	public function getSingleData($locale, $id)
	{
		$result = $this->model->query()->where('id', $id)->first();

		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
	}
}

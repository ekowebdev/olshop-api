<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\SearchLog;
use App\Exceptions\DataEmptyException;
use BaoPham\DynamoDb\RawDynamoDbQuery;
use Illuminate\Support\Facades\Request;
use BaoPham\DynamoDb\DynamoDbQueryBuilder;

class SearchLogRepository extends BaseRepository 
{
    private $repository_name = 'Search Log';
    private $model;

	public function __construct(SearchLog $model)
	{
		$this->model = $model;
	}

    public function getIndexData($locale)
    {
        $result = $this->model
                    ->query()
                    ->orderBy('created_at', 'desc')
                    ->limit(intval(Request::get('per_page') ?? 10))
                    ->get();
        if($result->isEmpty()) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;
    }

	public function getSingleData($locale, $id)
	{
		$result = $this->model
                    ->all()
                    ->where('id', $id)	
                    ->first();
		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;	
	}

    public function getDataByUser($locale, $id)
	{
        $id = intval($id);
		$result = $this->model
                    ->query()
                    ->orderBy('created_at', 'desc')
                    ->where('user_id', $id)	
                    ->limit(intval(Request::get('per_page') ?? 10))
                    ->get();
		if($result->isEmpty()) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;	
	}
}
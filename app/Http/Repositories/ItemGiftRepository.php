<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\ItemGift;
use App\Http\Models\SearchLog;
use App\Exceptions\DataEmptyException;
use BaoPham\DynamoDb\Facades\DynamoDb;
use Illuminate\Support\Facades\Request;

class ItemGiftRepository extends BaseRepository 
{
    private $repository_name = 'Item Gift';
    private $model;

	public function __construct(ItemGift $model)
	{
		$this->model = $model;
	}

    public function getIndexData($locale, array $sortable_and_searchable_column)
    {
        $this->validate(Request::all(), [
            'per_page' => ['numeric']
        ]);
        $result = $this->model
                    ->getAll()
                    ->setSortableAndSearchableColumn($sortable_and_searchable_column)
                    ->search()
                    ->sort()
                    ->orderByDesc('id')
                    ->paginate(Arr::get(Request::all(), 'per_page', 15));
        $result->sortableAndSearchableColumn = $sortable_and_searchable_column;
        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;
    }

	public function getSingleData($locale, $id)
	{
		$result = $this->model
                  ->getAll()
                  ->where($this->model->KeyPrimaryTable, $id)	
                  ->first();
		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;	
	}

    public function getSingleDataBySlug($locale, $slug)
	{
		$result = $this->model
                  ->getAll()
                  ->where('item_gift_slug', $slug)	
                  ->first();
		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;	
	}

    public function getSingleDataByCategory($locale, array $sortable_and_searchable_column, $category)
	{
        $this->validate(Request::all(), [
            'per_page' => ['numeric']
        ]);
		$result = $this->model
                ->getAll()
                ->whereHas('category', function ($query) use ($category) {
                    $query->where('category_slug', $category);
                })
                ->setSortableAndSearchableColumn($sortable_and_searchable_column)
                ->search()
                ->sort()
                ->orderByDesc('id')
                ->paginate(Arr::get(Request::all(), 'per_page', 15));
        $result->sortableAndSearchableColumn = $sortable_and_searchable_column;
        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;
	}

    public function getDataByBrand($locale, array $sortable_and_searchable_column, $brand)
	{
		$this->validate(Request::all(), [
            'per_page' => ['numeric']
        ]);
		$result = $this->model
                ->getAll()
                ->whereHas('brand', function ($query) use ($brand) {
                    $query->where('brand_slug', $brand);
                })
                ->setSortableAndSearchableColumn($sortable_and_searchable_column)
                ->search()
                ->sort()
                ->orderByDesc('id')
                ->paginate(Arr::get(Request::all(), 'per_page', 15));
        $result->sortableAndSearchableColumn = $sortable_and_searchable_column;
        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;	
	}

    public function getDataByUserRecomendation($locale, array $sortable_and_searchable_column)
	{
        $this->validate(Request::all(), [
            'total_search' => ['numeric'],
            'per_page' => ['numeric'],
        ]);

        $search_logs = SearchLog::query()->where('user_id', auth()->user()->id)->get();
        $search_logs = $search_logs->toArray();

        $output_array = [];

        // Urutkan $search_logs berdasarkan panjang search_text secara descending
        usort($search_logs, function ($a, $b) {
            return strlen($b['search_text']) - strlen($a['search_text']);
        });

        foreach ($search_logs as $item) {
            $search_text = $item["search_text"];
            // Cari key yang mirip
            $found = false;
            foreach ($output_array as $key => $value) {
                similar_text($search_text, $key, $percent);
                // Jika ada yang mirip, tambahkan ke nilai yang sudah ada
                if ($percent >= 65) {
                    $output_array[$key] += 1;
                    $found = true;
                    break;
                }
            }
            // Jika tidak ada yang mirip, buat key baru
            if (!$found) {
                $output_array[$search_text] = 1;
            }
        }

        $min_search = Arr::get(Request::all(), 'min_search', 5);

        $final_array = array_filter($output_array, function ($count) use ($min_search) {
            return $count >= $min_search;
        });
        $final_array = array_keys($final_array);

        if(count($final_array) == 0){
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        }

        $item_gifts = $this->model->getAll();
        foreach ($final_array as $arr) {
            $item_gifts->orWhere('item_gift_name', 'LIKE', '%' . $arr . '%');
        }
        $result = $item_gifts
                    ->setSortableAndSearchableColumn($sortable_and_searchable_column)
                    ->search()
                    ->sort()
                    ->orderByDesc('id')
                    ->groupBy('id')
                    ->paginate(Arr::get(Request::all(), 'per_page', 15));
        $result->sortableAndSearchableColumn = $sortable_and_searchable_column;
        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;	
	}
}
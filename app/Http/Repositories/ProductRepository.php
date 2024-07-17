<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\Product;
use App\Http\Models\SearchLog;
use Illuminate\Support\Benchmark;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class ProductRepository extends BaseRepository
{
    private $repository_name = 'Product';
    private $model;

	public function __construct(Product $model)
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
                  ->where('slug', $slug)
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
                ->whereHas('categories', function ($query) use ($category) {
                    $query->where('slug', $category);
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
                ->whereHas('brands', function ($query) use ($brand) {
                    $query->where('slug', $brand);
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

        $search_logs = SearchLog::LastMonth()->where('user_id', auth()->user()->id)->get()->toArray();

        $match_products = [];

        // Urutkan $search_logs berdasarkan panjang search_text secara descending
        usort($search_logs, function ($a, $b) {
            return strlen($b['search_text']) - strlen($a['search_text']);
        });

        foreach ($search_logs as $item) {
            $search_text = $item["search_text"];
            // Cari key yang mirip
            $found = false;
            foreach ($match_products as $key => $value) {
                similar_text($search_text, $key, $percent);
                // Jika ada yang mirip, tambahkan ke nilai yang sudah ada
                if ($percent >= 65) {
                    $match_products[$key] += 1;
                    $found = true;
                    break;
                }
            }
            // Jika tidak ada yang mirip, buat key baru
            if (!$found) {
                $match_products[$search_text] = 1;
            }
        }

        $min_search = Arr::get(Request::all(), 'min_search', 5);

        $products = array_filter($match_products, function ($count) use ($min_search) {
            return $count >= $min_search;
        });
        $products = array_keys($products);

        if(count($products) == 0){
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        }

        $result = $this->model
                    ->getAll()
                    ->where(function ($query) use ($products) {
                        foreach ($products as $item) {
                            $query->orWhere('name', 'LIKE', '%' . $item . '%');
                        }
                    })
                    ->setSortableAndSearchableColumn($sortable_and_searchable_column)
                    ->search()
                    ->sort()
                    ->groupBy('name')
                    ->havingRaw('total_order = MAX(total_order)')
                    ->paginate(Arr::get(Request::all(), 'per_page', 15));
        $result->sortableAndSearchableColumn = $sortable_and_searchable_column;
        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;
	}

    public function search($locale)
	{
        $this->validate(Request::all(), [
            'search' => ['string'],
            'per_page' => ['numeric'],
        ]);

        $result = $this->model
                    ->search(Request::get('search'))
                    ->get();

        if(!$result) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));

        return $result;
	}
}

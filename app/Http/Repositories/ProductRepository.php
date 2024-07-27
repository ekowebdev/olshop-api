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
    private $repository = 'Product';
    private $model;

	public function __construct(Product $model)
	{
		$this->model = $model;
	}

    public function index($locale, array $sortableAndSearchableColumn)
    {
        $this->validate(Request::all(), [
            'per_page' => ['numeric']
        ]);

        $result = $this->model
                ->getAll()
                ->setSortableAndSearchableColumn($sortableAndSearchableColumn)
                ->search()
                ->sort()
                ->orderByDesc('id')
                ->paginate(Arr::get(Request::all(), 'per_page', 15));

        $result->sortableAndSearchableColumn = $sortableAndSearchableColumn;

        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
    }

	public function getSingleData($locale, $id)
	{
		$result = $this->model->getAll()->where($this->model->KeyPrimaryTable, $id)->first();

		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
	}

    public function getSingleDataBySlug($locale, $slug)
	{
		$result = $this->model->getAll()->where('slug', $slug)->first();

		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
	}

    public function getSingleDataByCategory($locale, array $sortableAndSearchableColumn, $category)
	{
        $this->validate(Request::all(), [
            'per_page' => ['numeric']
        ]);

		$result = $this->model
                ->getAll()
                ->whereHas('categories', function ($query) use ($category) {
                    $query->where('slug', $category);
                })
                ->setSortableAndSearchableColumn($sortableAndSearchableColumn)
                ->search()
                ->sort()
                ->orderByDesc('id')
                ->paginate(Arr::get(Request::all(), 'per_page', 15));

        $result->sortableAndSearchableColumn = $sortableAndSearchableColumn;

        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
	}

    public function getDataByBrand($locale, array $sortableAndSearchableColumn, $brand)
	{
		$this->validate(Request::all(), [
            'per_page' => ['numeric']
        ]);

		$result = $this->model
                ->getAll()
                ->whereHas('brands', function ($query) use ($brand) {
                    $query->where('slug', $brand);
                })
                ->setSortableAndSearchableColumn($sortableAndSearchableColumn)
                ->search()
                ->sort()
                ->orderByDesc('id')
                ->paginate(Arr::get(Request::all(), 'per_page', 15));

        $result->sortableAndSearchableColumn = $sortableAndSearchableColumn;

        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
	}

    public function getDataByUserRecomendation($locale, array $sortableAndSearchableColumn)
	{
        $this->validate(Request::all(), [
            'total_search' => ['numeric'],
            'per_page' => ['numeric'],
        ]);

        $searchLogs = SearchLog::LastMonth()->where('user_id', auth()->user()->id)->get()->toArray();

        $matchProducts = [];

        // Urutkan $searchLogs berdasarkan panjang search_text secara descending
        usort($searchLogs, function ($a, $b) {
            return strlen($b['search_text']) - strlen($a['search_text']);
        });

        foreach ($searchLogs as $item) {
            $searchText = $item["search_text"];
            // Cari key yang mirip
            $found = false;
            foreach ($matchProducts as $key => $value) {
                similar_text($searchText, $key, $percent);
                // Jika ada yang mirip, tambahkan ke nilai yang sudah ada
                if ($percent >= 65) {
                    $matchProducts[$key] += 1;
                    $found = true;
                    break;
                }
            }
            // Jika tidak ada yang mirip, buat key baru
            if (!$found) {
                $matchProducts[$searchText] = 1;
            }
        }

        $minSearch = Arr::get(Request::all(), 'min_search', 5);

        $products = array_filter($matchProducts, function ($count) use ($minSearch) {
            return $count >= $minSearch;
        });
        $products = array_keys($products);

        if(count($products) == 0){
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));
        }

        $result = $this->model
                    ->getAll()
                    ->where(function ($query) use ($products) {
                        foreach ($products as $item) {
                            $query->orWhere('name', 'LIKE', '%' . $item . '%');
                        }
                    })
                    ->setSortableAndSearchableColumn($sortableAndSearchableColumn)
                    ->search()
                    ->sort()
                    ->groupBy('name')
                    ->havingRaw('total_order = MAX(total_order)')
                    ->paginate(Arr::get(Request::all(), 'per_page', 15));

        $result->sortableAndSearchableColumn = $sortableAndSearchableColumn;

        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

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

        if(!$result) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
	}
}

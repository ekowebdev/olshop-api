<?php

use DB;
use Illuminate\Support\Str;
use App\Http\Models\Review;
use App\Http\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use App\Http\Resources\NotificationResource;
use Illuminate\Pagination\LengthAwarePaginator;

function rounded_rating($rating)
{
    $roundedRating = round($rating * 2) / 2;
    return number_format($roundedRating, 1);
}

function format_money($number)
{
    return 'Rp. ' . number_format($number, 2, ",", ".");
}

function is_multidimensional_array($array) {
    if (!is_array($array)) {
        return false;
    }

    foreach ($array as $element) {
        if (is_array($element)) {
            return true;
        }
    }

    return false;
}

function store_notification($data = [])
{
    DB::beginTransaction();

    $model = new Notification();
    $check = $model->Unread()->where('user_id', $data['user_id']);
    $model->id = Str::uuid();
    $model->title = $data['title'];
    $model->text = $data['text'];
    $model->user_id = (int) $data['user_id'];
    $model->type = (int) $data['type'];
    $model->status_read = (int) $data['status_read'];
    if($data['type'] == 0){
        $model->url = config('setting.frontend.url') . '/accounts';
        $model->icon = '';
        $model->background_color = "#E9FBE9";
        $model->save();
    } else if($data['type'] == 1){
        $typeInfo = $check->where('type', 1)->get()->toArray();
        if(count($typeInfo) == 0) {
            $model->url = config('setting.frontend.url') . '/accounts/profile';
            $model->icon = '';
            $model->background_color = "#E0E7EC";
            $model->save();
        }
    }

    $notification = $model->where('user_id', $data['user_id'])->orderBy('created_at', 'desc')->with('users')->limit(5)->get();
    $results = collect($notification)->map(function($data) {
        return [
            'id' => $data->id,
            'title' => $data->title,
            'text' => $data->text,
            'url' => $data->url,
            'type' => $data->type,
            'icon' => $data->icon,
            'background_color' => $data->background_color,
            'status_read' => $data->status_read,
            'date' => $data->date,
            'users' => (!$data->users) ? null : $data->users->makeHidden(['email_verified_at', 'google_access_token', 'created_at', 'updated_at'])->toArray(),
        ];
    });

    DB::commit();

    return $results;
}

function is_json($string = "") {
    if(!is_string($string) || empty($string) || $string == "[]") return false;
    $res = json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE && $res != $string);
}

function format_json($originalData, $page = null, $perPage = 10, $options = [])
{
    $dataCollection = paginate($originalData, $page, $perPage, $options);

    $transformedData = $dataCollection->map(function ($item) {
        return $item;
    });

    $dataArray = $dataCollection->toArray();

    $results = [
        'data' => $transformedData->toArray(),
        'links' => [
            'first' => $dataArray['first_page_url'],
            'last' => $dataArray['last_page_url'],
            'prev' => $dataArray['prev_page_url'],
            'next' => $dataArray['next_page_url'],
        ],
        'meta' => [
            'current_page' => $dataArray['current_page'],
            'from' => $dataArray['from'],
            'last_page' => $dataArray['last_page'],
            'links' => $dataArray['links'],
            'path' => $dataArray['path'],
            'per_page' => $dataArray['per_page'],
            'to' => $dataArray['to'],
            'total' => $dataArray['total'],
        ],
    ];

    return $results;
}

function paginate($data, $page = null, $perPage = 10, $options = [])
{
    $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
    $data = $data instanceof Collection ? $data : Collection::make($data);
    return new LengthAwarePaginator($data->forPage($page, $perPage), $data->count(), $perPage, $page, $options);
}

function format_product_weight($product)
{
    $weight = $product->variants->pluck('weight')->toArray();
    if (count($weight) == 1) {
        return (string) $weight[0] . ' Gram';
    } elseif (count($weight) > 1) {
        $weight = min($weight);
        return (string) $weight . ' Gram';
    } else {
        return (string) $product->weight ?? 0 . ' Gram';
    }
}

function format_product_point($product)
{
    $points = $product->variants->pluck('point')->toArray();
    if (count($points) == 1) {
        return format_money((string) $points[0]);
    } elseif (count($points) > 1) {
        $minValue = min($points);
        $maxValue = max($points);
        if ($minValue === $maxValue) {
            return format_money((string) $minValue);
        }
        return format_money($minValue) . " ~ " . format_money($maxValue);
    } else {
        return format_money((string) $product->point ?? 0);
    }
}

function is_reviewed($productId, $orderId)
{
    $userId = (auth()->user()) ? auth()->user()->id : 0;
    $reviews = Review::where('user_id', $userId)
        ->where('product_id', $productId)
        ->where('order_id', $orderId)
        ->get();
    return (count($reviews) > 0) ? 1 : 0;
}

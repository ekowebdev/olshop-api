<?php

use Illuminate\Support\Str;
use App\Http\Models\Product;
use App\Http\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Repositories\NotificationRepository;

function rounded_rating($rating)
{
    $rounded_rating = round($rating * 2) / 2;
    return number_format($rounded_rating, 1);
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

function store_notification(array $data)
{
    \DB::beginTransaction();
    $model = new Notification();
    $repository = new NotificationRepository($model);
    $check = $model->query()->where('user_id', $data['user_id'])->where('status_read', 0);
    $model->id = strval(Str::uuid());
    $model->title = $data['title'];
    $model->text = $data['text'];
    $model->user_id = intval($data['user_id']);
    $model->type = intval($data['type']);
    $model->status_read = intval($data['status_read']);
    if($data['type'] == 0){
        $model->url = config('setting.frontend.url') . '/accounts';
        $model->icon = '';
        $model->background_color = "#E9FBE9";
        $model->save();
    } else if($data['type'] == 1){
        $type_info = $check->where('type', 1)->get()->toArray();
        if(count($type_info) == 0) {
            $model->url = config('setting.frontend.url') . '/accounts/profile';
            $model->icon = '';
            $model->background_color = "#E0E7EC";
            $model->save();
        }
    }
    $notification = $model->where(['user_id' => $data['user_id'], 'status_read' => 0])->get();
    \DB::commit();
    return $notification;
}

function is_json($string) {
    if(!is_string($string) || empty($string) || $string == "[]") return false;
    $res = json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE && $res != $string);
}

function format_json($original_data, $page, $per_page, $options)
{
    $data_collection = paginate($original_data, $page, $per_page, $options);

    $transformed_data = $data_collection->map(function ($item) {
        return $item;
    });

    $data_array = $data_collection->toArray();

    $results = [
        'data' => $transformed_data->toArray(),
        'links' => [
            'first' => $data_array['first_page_url'],
            'last' => $data_array['last_page_url'],
            'prev' => $data_array['prev_page_url'],
            'next' => $data_array['next_page_url'],
        ],
        'meta' => [
            'current_page' => $data_array['current_page'],
            'from' => $data_array['from'],
            'last_page' => $data_array['last_page'],
            'links' => $data_array['links'],
            'path' => $data_array['path'],
            'per_page' => $data_array['per_page'],
            'to' => $data_array['to'],
            'total' => $data_array['total'],
        ],
    ];

    return $results;
}

function paginate($data, $page = null, $per_page = 15, $options = [])
{
    $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
    $data = $data instanceof Collection ? $data : Collection::make($data);
    return new LengthAwarePaginator($data->forPage($page, $per_page), $data->count(), $per_page, $page, $options);
}

<?php

use Illuminate\Support\Str;
use App\Http\Models\Notification;

function calculate_rating($rating)
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

function store_notification($data = [])
{
    $model = new Notification();
    $check = $model->query()->where('user_id', $data['user_id'])->where('status_read', 0);
    $model->id = strval(Str::uuid());
    $model->title = $data['title'];
    $model->text = $data['text'];
    $model->user_id = intval($data['user_id']);
    $model->type = intval($data['type']);
    $model->status_read = intval($data['status_read']);
    if($data['type'] == 0){
        $model->url = env('FRONT_URL') . '/transaction';
        $model->icon = '';
        $model->background_color = "#E9FBE9";
        $model->save();
    } else if($data['type'] == 1){
        $type_alert = $check->where('type', 1)->get()->toArray();
        if(count($type_alert) == 0) {
            $model->url = env('FRONT_URL') . '/my-voucher';
            $model->icon = '';
            $model->background_color = "#FEE8E6";
            $model->save();
        }
    } else if($data['type'] == 2){
        $type_promo = $check->where('type', 2)->get()->toArray();
        if(count($type_promo) == 0) {
            $model->url = env('FRONT_URL') . '/account';
            $model->icon = '';
            $model->background_color = "#E0E7EC";
            $model->save();
        }
    }

    $notification = $model->query()->where('user_id', $data['user_id'])->get()->toArray();
    return $notification;
}
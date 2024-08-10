<?php

use Illuminate\Support\Str;
use App\Http\Models\Review;
use App\Http\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;
use App\Http\Resources\NotificationResource;
use Illuminate\Pagination\LengthAwarePaginator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

function roundedRating($rating)
{
    $roundedRating = round($rating * 2) / 2;
    return number_format($roundedRating, 1);
}

function formatMoney($number)
{
    return 'Rp. ' . number_format($number, 2, ",", ".");
}

function isMultidimensionalArray($array) {
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

function storeNotification($data = [])
{
    \DB::beginTransaction();

    $model = new Notification();
    $check = $model->Unread()->where('user_id', $data['user_id']);
    $model->id = (string) Str::uuid();
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
            'fdate' => $data->fdate,
            'users' => (!$data->users) ? null : $data->users->makeHidden(['email_verified_at', 'google_access_token', 'created_at', 'updated_at'])->toArray(),
        ];
    });

    \DB::commit();

    return $results;
}

function isJson($string = "") {
    if(!is_string($string) || empty($string) || $string == "[]") return false;
    $res = json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE && $res != $string);
}

function formatJson($originalData, $page = null, $perPage = 10, $options = [])
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

function formatProductWeight($product)
{
    $weight = $product->variants->pluck('weight')->toArray();
    if (count($weight) == 1) {
        return (string) $weight[0] . ' Gram';
    } elseif (count($weight) > 1) {
        $weight = min($weight);
        return (string) $weight . ' Gram';
    } else {
        return (string) $product->weight . ' Gram';
    }
}

function formatProductPoint($product)
{
    $points = $product->variants->pluck('point')->toArray();
    if (count($points) == 1) {
        return formatMoney((string) $points[0]);
    } elseif (count($points) > 1) {
        $minValue = min($points);
        $maxValue = max($points);
        if ($minValue === $maxValue) {
            return formatMoney((string) $minValue);
        }
        return formatMoney($minValue) . " ~ " . formatMoney($maxValue);
    } else {
        return formatMoney((string) $product->point);
    }
}

function isReviewed($productId, $orderId)
{
    $userId = (auth()->user()) ? auth()->user()->id : 0;
    $reviews = Review::where('user_id', $userId)
        ->where('product_id', $productId)
        ->where('order_id', $orderId)
        ->get();
    return (count($reviews) > 0) ? 1 : 0;
}

function uploadImagesToCloudinary($file, $path)
{
    $customName = time();

    $cloudinaryImage = Cloudinary::upload($file->getRealPath(), ['folder' => config('services.cloudinary.folder') . '/images/'. $path, 'public_id' => $customName]);
    $publicImageId = $cloudinaryImage->getPublicId();

    $image = Image::make($file);
    $imgThumb = $image->crop(5, 5);

    $tempFilePath = tempnam(sys_get_temp_dir(), 'thumbnail') . '.jpg';
    $imgThumb->save($tempFilePath);

    $cloudinaryThumb = Cloudinary::upload($tempFilePath, ['folder' => config('services.cloudinary.folder') . '/images/'. $path .'/thumbnails', 'public_id' => $customName . '_thumb']);

    File::delete($tempFilePath);

    return $customName . '.' . strtolower($file->getClientOriginalExtension());
}

function deleteImagesFromCloudinary($image, $path)
{
    $folder = config('services.cloudinary.folder');
    $previousPublicId = explode('.', $image)[0];
    Cloudinary::destroy("$folder/images/$path/$previousPublicId");
    Cloudinary::destroy("$folder/images/$path/thumbnails/{$previousPublicId}_thumb");
}

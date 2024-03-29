<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Http\Models\Notification;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\NotificationRepository;

class NotificationService extends BaseService
{
    private $model, $repository;

    public function __construct(Notification $model, NotificationRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        return $this->repository->getIndexData($locale);
    }

    public function getSingleData($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function getDataByUser($locale, $id)
    {
        return $this->repository->getDataByUser($locale, $id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'user_id' => $check_data->user_id,
            'title' => $check_data->title,
            'text' => $check_data->text,
            'type' => $check_data->type,
            'url' => $check_data->url,
            'icon' => $check_data->icon,
            'background_color' => $check_data->background_color,
            'status_read' => $check_data->status_read,
        ], $data);

        $data_request = Arr::only($data, [
            'title',
            'text',
            'url',
            'user_id',
            'type',
            'icon',
            'background_color',
            'status_read',
        ]);

        $this->repository->validate($data_request, [
            'user_id' => [
                'exists:users,id',
            ],
            'text' => [
                'string',
            ],
            'title' => [
                'string',
            ],
            'type' => [
                'integer',
                'in:0,1,2',
            ],
            'url' => [
                'string',
            ],
            'background_color' => [
                'string',
            ],
            'status_read' => [
                'integer',
                'in:0,1',
            ],
        ]);

        DB::beginTransaction();
        $data_request['title'] = $data_request['title'];
        $data_request['text'] = $data_request['text'];
        $data_request['type'] = intval($data_request['type']);
        $data_request['status_read'] = intval($data_request['status_read']);
        $data_request['url'] = $data_request['url'];
        $data_request['icon'] = $data_request['icon'];
        $data_request['background_color'] = $data_request['background_color'];
        $check_data->update($data_request);
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        
        DB::beginTransaction();
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
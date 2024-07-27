<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Http\Models\Notification;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\NotificationResource;
use App\Http\Repositories\NotificationRepository;

class NotificationService extends BaseService
{
    private $model, $repository;

    public function __construct(Notification $model, NotificationRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function index($locale, $request)
    {
        $data = $this->repository->getAllData($locale);
        $totalData = $this->model->count();
        $totalRead = $this->model->Read()->count();
        $totalUnread = $this->model->Unread()->count();
        return (NotificationResource::collection($data))->additional([
                    'summary' => [
                        'total_data' => $totalData,
                        'total_read' => $totalRead,
                        'total_unread' => $totalUnread
                    ]
                ]);
    }

    public function show($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function showByUser($locale, $id)
    {
        $id = (int) $id;

        $data = $this->repository->getDataByUser($locale, $id);

        $totalData = $this->model->where('user_id', $id)->count();
        $totalRead = $this->model->where('user_id', $id)->Read()->count();
        $totalUnread = $this->model->where('user_id', $id)->Unread()->count();

        return (NotificationResource::collection($data))->additional([
                    'summary' => [
                        'total_data' => $totalData,
                        'total_read' => $totalRead,
                        'total_unread' => $totalUnread
                    ]
                ]);
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'user_id' => $checkData->user_id,
            'title' => $checkData->title,
            'text' => $checkData->text,
            'type' => $checkData->type,
            'url' => $checkData->url,
            'icon' => $checkData->icon,
            'background_color' => $checkData->background_color,
            'status_read' => $checkData->status_read,
        ], $data);

        $request = Arr::only($data, [
            'title',
            'text',
            'url',
            'user_id',
            'type',
            'icon',
            'background_color',
            'status_read',
        ]);

        $this->repository->validate($request, [
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

        $request['type'] = (int) $request['type'];
        $request['status_read'] = (int) $request['status_read'];
        $checkData->update($request);

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();

        $result = $checkData->delete();

        DB::commit();

        return $result;
    }
}

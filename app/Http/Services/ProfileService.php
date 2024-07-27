<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Http\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\ProfileRepository;

class ProfileService extends BaseService
{
    private $model, $repository;

    public function __construct(Profile $model, ProfileRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function index($locale, $data)
    {
        $search = [
            'user_id' => 'user_id',
            'name' => 'name',
            'birthdate' => 'birthdate',
            'phone_number' => 'phone_number',
        ];

        $searchColumn = [
            'id' => 'id',
            'user_id' => 'user_id',
            'name' => 'name',
            'birthdate' => 'birthdate',
            'phone_number' => 'phone_number',
        ];

        $sortableAndSearchableColumn = [
            'search'        => $search,
            'search_column' => $searchColumn,
            'sort_column'   => array_merge($search, $searchColumn),
        ];

        return $this->repository->getAllData($locale, $sortableAndSearchableColumn);
    }

    public function show($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function store($locale, $data)
    {
        $request = Arr::only($data, [
            'user_id',
            'name',
            'birthdate',
            'phone_number',
            'avatar',
        ]);

        $this->repository->validate($request, [
                'user_id' => [
                    'required',
                    'exists:users,id',
                    'unique:profiles,user_id',
                ],
                'name' => [
                    'required',
                    'string',
                ],
                'birthdate' => [
                    'required',
                    'date',
                ],
                'phone_number' => [
                    'required',
                    'numeric',
                ],
                'avatar' => [
                    'max:1000',
                    'image',
                    'mimes:jpg,png',
                ],
            ]
        );

        DB::beginTransaction();

        $file = Request::file('avatar');

        $imageName = uploadImagesToCloudinary($file, 'profiles');

        $result = $this->model->create([
            'user_id' => $request['user_id'],
            'name' => $request['name'],
            'birthdate' => $request['birthdate'],
            'phone_number' => $request['phone_number'],
            'avatar' => $imageName ?? null,
        ]);

        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $request = Arr::only($data, [
            'user_id',
            'name',
            'birthdate',
            'phone_number',
            'avatar',
        ]);

        $this->repository->validate($request, [
            'user_id' => [
                'exists:users,id',
                'unique:profiles,user_id,'.$id,
            ],
            'name' => [
                'string',
            ],
            'birthdate' => [
                'date',
            ],
            'phone_number' => [
                'numeric',
            ],
            'avatar' => [
                'max:1000',
                'image',
                'mimes:jpg,png',
            ],
        ]);

        DB::beginTransaction();

        if (isset($request['avatar'])) {
            $file = Request::file('avatar');

            if ($checkData->image) {
                deleteImagesFromCloudinary($checkData->avatar, 'profiles');
            }

            $imageName = uploadImagesToCloudinary($file, 'profiles');

            $checkData->avatar = $imageName;
        }

        $checkData->user_id = $request['user_id'] ?? $checkData->user_id;
        $checkData->name = $request['name'] ?? $checkData->name;
        $checkData->birthdate = $request['birthdate'] ?? $checkData->birthdate;
        $checkData->phone_number = $request['phone_number'] ?? $checkData->phone_number;
        $checkData->save();

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();
        deleteImagesFromCloudinary($checkData->avatar, 'profiles');
        $result = $checkData->delete();
        DB::commit();

        return $result;
    }
}

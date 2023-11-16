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

    public function getIndexData($locale, $data)
    {
        $search = [
            'user_id' => 'user_id',
            'name' => 'name',
            'birthdate' => 'birthdate',
            'phone_number' => 'phone_number',
        ];

        $search_column = [
            'id' => 'id',
            'user_id' => 'user_id',
            'name' => 'name',
            'birthdate' => 'birthdate',
            'phone_number' => 'phone_number',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];
        
        return $this->repository->getIndexData($locale, $sortable_and_searchable_column);
    }

    public function getSingleData($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function store($locale, $data)
    {
        $data_request = Arr::only($data, [
            'user_id',
            'name',
            'birthdate',
            'phone_number',
            'avatar',
        ]);

        $this->repository->validate($data_request, [
                'user_id' => [
                    'required',
                    'exists:users,id',
                    'unique:profiles,user_id',
                ],
                
                'birthdate' => [
                    'required',
                    'date',
                ],
                'phone_number' => [
                    'required',
                    'numeric','name' => [
                    'required',
                    'string',
                ],
                ],
                'avatar' => [
                    'required',
                    'max:1000',
                    'image',
                    'mimes:jpg,png',
                ],
            ]
        );

        DB::beginTransaction();
        $image = $data_request['avatar'];
        $image_name = time() . '.' . $image->getClientOriginalExtension();
        Storage::disk('s3')->put('images/avatar/' . $image_name, file_get_contents($image));
        $result = $this->model->create([
            'user_id' => $data_request['user_id'],
            'name' => $data_request['name'],
            'birthdate' => $data_request['birthdate'],
            'phone_number' => $data_request['phone_number'],
            'avatar' => $image_name,
        ]);
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data_request = Arr::only($data, [
            'user_id',
            'name',
            'birthdate',
            'phone_number',
            'avatar',
        ]);

        $this->repository->validate($data_request, [
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
        if (isset($data_request['avatar'])) {
            if(Storage::disk('s3')->exists('images/avatar/' . $check_data->avatar)) {
                Storage::disk('s3')->delete('images/avatar/' . $check_data->avatar);
            }
            $image = $data_request['avatar'];
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            Storage::disk('s3')->put('images/avatar/' . $image_name, file_get_contents($image));
            $check_data->avatar = $image_name;
        }
        $check_data->user_id = $data_request['user_id'] ?? $check_data->user_id;
        $check_data->name = $data_request['name'] ?? $check_data->name;
        $check_data->birthdate = $data_request['birthdate'] ?? $check_data->birthdate;
        $check_data->phone_number = $data_request['phone_number'] ?? $check_data->phone_number;
        $check_data->save();
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        DB::beginTransaction();
        if(Storage::disk('s3')->exists('images/avatar/' . $check_data->avatar)) {
            Storage::disk('s3')->delete('images/avatar/' . $check_data->avatar);
        }
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
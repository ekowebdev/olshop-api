<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Http\Models\Address;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\AddressRepository;

class AddressService extends BaseService
{
    private $model, $repository, $userRepository;

    public function __construct(Address $model, AddressRepository $repository, UserRepository $userRepository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->userRepository = $userRepository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'user_id' => 'user_id',
            'person_name' => 'person_name',
            'person_phone' => 'person_phone',
            'province_id' => 'province_id',
            'city_id' => 'city_id',
            'postal_code' => 'postal_code',
            'street' => 'street',
        ];

        $search_column = [
            'id' => 'id',
            'user_id' => 'user_id',
            'person_name' => 'person_name',
            'person_phone' => 'person_phone',
            'province_id' => 'province_id',
            'city_id' => 'city_id',
            'postal_code' => 'postal_code',
            'street' => 'street',
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
            'person_name',
            'person_phone',
            'province_id',
            'city_id',
            'subdistrict_id',
            'postal_code',
            'street',
        ]);

        $this->repository->validate($data_request, [
                'user_id' => [
                    'required',
                    'exists:users,id',
                ],
                'person_name' => [
                    'required',
                    'string',
                ],
                'person_phone' => [
                    'required',
                ],
                'province_id' => [
                    'required',
                    'integer',
                ],
                'city_id' => [
                    'required',
                    'integer',
                ],
                'subdistrict_id' => [
                    'nullable',
                    'integer',
                ],
                'postal_code' => [
                    'required',
                    'numeric',
                ],
                'street' => [
                    'required',
                    'string',
                ],
            ]
        );

        DB::beginTransaction();
        $result = $this->model->create($data_request);
        if($this->repository->countDataByUser($data_request['user_id']) == 1) {
            $user = $this->userRepository->getSingleData($locale, $data_request['user_id']);
            $user->main_address_id = $result->id;
            $user->save();
        }
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'user_id' => $check_data->user_id,
            'person_name' => $check_data->person_name,
            'person_phone' => $check_data->person_phone,
            'province_id' => $check_data->province_id,
            'city_id' => $check_data->city_id,
            'subdistrict_id' => $check_data->subdistrict_id,
            'postal_code' => $check_data->postal_code,
            'street' => $check_data->street,
        ], $data);

        $data_request = Arr::only($data, [
            'user_id',
            'person_name',
            'person_phone',
            'province_id',
            'city_id',
            'subdistrict_id',
            'postal_code',
            'street',
        ]);

        $this->repository->validate($data_request, [
            'user_id' => [
                'exists:users,id',
            ],
            'person_name' => [
                'string',
            ],
            'province_id' => [
                'integer',
            ],
            'city_id' => [
                'integer',
            ],
            'subdistrict_id' => [
                'nullable',
                'integer',
            ],
            'postal_code' => [
                'numeric',
            ],
            'street' => [
                'string',
            ],
        ]);

        DB::beginTransaction();
        $check_data->update($data_request);
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        DB::beginTransaction();
        if($this->repository->countDataByUser($check_data->user_id) == 1 || $check_data->is_main == 1) {
            throw new ValidationException(json_encode(['address' => [trans('error.cannot_delete_primary_address')]]));
        }
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
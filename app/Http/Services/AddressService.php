<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Http\Models\Address;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ConflictException;
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

    public function index($locale, $data)
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

        $searchColumn = [
            'id' => 'id',
            'user_id' => 'user_id',
            'person_name' => 'person_name',
            'person_phone' => 'person_phone',
            'province_id' => 'province_id',
            'city_id' => 'city_id',
            'postal_code' => 'postal_code',
            'street' => 'street',
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
            'person_name',
            'person_phone',
            'province_id',
            'city_id',
            'subdistrict_id',
            'postal_code',
            'street',
        ]);

        $this->repository->validate($request, [
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
        ]);

        DB::beginTransaction();

        $result = $this->model->create($request);

        if($this->repository->countDataByUser($request['user_id']) == 1) {
            $user = $this->userRepository->getSingleData($locale, $request['user_id']);
            $user->main_address_id = $result->id;
            $user->save();
        }

        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'user_id' => $checkData->user_id,
            'person_name' => $checkData->person_name,
            'person_phone' => $checkData->person_phone,
            'province_id' => $checkData->province_id,
            'city_id' => $checkData->city_id,
            'subdistrict_id' => $checkData->subdistrict_id,
            'postal_code' => $checkData->postal_code,
            'street' => $checkData->street,
        ], $data);

        $request = Arr::only($data, [
            'user_id',
            'person_name',
            'person_phone',
            'province_id',
            'city_id',
            'subdistrict_id',
            'postal_code',
            'street',
        ]);

        $this->repository->validate($request, [
            'user_id' => [
                'exists:users,id',
            ],
            'person_name' => [
                'string',
            ],
            'province_id' => [
                'integer',
                'exists:provinces,id',
            ],
            'city_id' => [
                'integer',
                'exists:cities,id',
            ],
            'subdistrict_id' => [
                'nullable',
                'integer',
                'exists:subdistricts,id',
            ],
            'postal_code' => [
                'numeric',
            ],
            'street' => [
                'string',
            ],
        ]);

        DB::beginTransaction();

        $checkData->update($request);

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();

        if($this->repository->countDataByUser($checkData->user_id) == 1 || $checkData->is_main == 1) throw new ConflictException(trans('error.cannot_delete_primary_address'));

        $result = $checkData->delete();

        DB::commit();

        return $result;
    }
}

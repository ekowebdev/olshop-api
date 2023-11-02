<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Http\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService extends BaseService
{
    private $model, $repository;

    public function __construct(User $model, UserRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'name' => 'name',
            'username' => 'username',
            'birthdate' => 'birthdate',
            'email' => 'email',
        ];

        $search_column = [
            'id' => 'id',
            'name' => 'name',
            'username' => 'username',
            'email' => 'email',
            'birthdate' => 'birthdate',
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
            'name',
            'username',
            'email',
            'birthdate',
            'password',
            'role',
        ]);

        $this->repository->validate($data_request, [
                'name' => [
                    'required'
                ],
                'username' => [
                    'required',
                    'min:5',
                    'max:20',
                    'unique:users,username',
                ],
                'email' => [
                    'required',
                    'email:rfc,dns',
                    'unique:users,email'
                ],
                'birthdate' => [
                    'nullable',
                    'date',
                ],
                'password' => [
                    'required',
                    'min:6',
                    'max:20',
                ],
                'role.*' => [
                    'in:admin,customer',
                ],
            ]
        );

        DB::beginTransaction();
        $data_request['role'] = isset($data_request['role']) ? $data_request['role'] : 'customer';
        $data_request['password'] = Hash::make($data_request['password']);
        $result = $this->model->create($data_request);
        $result->assignRole($data_request['role']);
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'name' => $check_data->name,
            'username' => $check_data->username,
            'email' => $check_data->email,
            'birthdate' => $check_data->birthdate,
        ], $data);

        $data_request = Arr::only($data, [
            'name',
            'username',
            'email',
            'birthdate',
            'password',
            'role',
        ]);

        $this->repository->validate($data_request, [
            'username' => [
                'min:5',
                'max:20',
                'unique:users,username,'.$check_data->id,
            ],
            'email' => [
                'email',
                'unique:users,email,'.$check_data->id,
            ],
            'birthdate' => [
                'nullable',
                'date',
            ],
            'password' => [
                'sometimes',
                'min:6',
                'max:20',
            ],
            'role.*' => [
                'in:admin,customer',
            ],
        ]);

        $roles = auth()->user()->getRoleNames()->toArray();

        if(count($roles) == 1 && in_array('customer', $roles)){
            unset($data_request['role']);
        }

        DB::beginTransaction();
        if(!empty($data_request['password'])){
            $data_request['password'] = Hash::make($data_request['password']);
        } else {
            unset($data_request['password']);
        }
        $check_data->update($data_request);
        if(!empty($data_request['role'])){
            $roles = Role::whereIn('name', $data_request['role'])->get();
            $check_data->syncRoles($roles);
        }
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

    public function set_main_address($locale, $data)
    {
        $data_request = Arr::only($data, [
            'user_id',
            'address_id',
        ]);

        $this->repository->validate($data_request, [
            'user_id' => [
                'required',
                'exists:users,id',
            ],
            'address_id' => [
                'required',
                'exists:addresses,id',
            ],
        ]);

        DB::beginTransaction();
        $check_data = $this->repository->getSingleData($locale, $data['user_id']);
        $check_data->main_address_id = $data_request['address_id'];
        $check_data->save();
        DB::commit();

        return $this->repository->getSingleData($locale, $check_data->id);
    }
}
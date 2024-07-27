<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Http\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Http\Repositories\UserRepository;

class UserService extends BaseService
{
    private $model, $modelRole, $repository;

    public function __construct(User $model, Role $modelRole, UserRepository $repository)
    {
        $this->model = $model;
        $this->modelRole = $modelRole;
        $this->repository = $repository;
    }

    public function index($locale, $data)
    {
        $search = [
            'username' => 'username',
            'email' => 'email',
        ];

        $searchColumn = [
            'id' => 'id',
            'username' => 'username',
            'email' => 'email',
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
            'username',
            'email',
            'password',
            'role',
        ]);

        $this->repository->validate($request, [
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
            'password' => [
                'required',
                'min:6',
                'max:20',
            ],
            'role' => [
                'in:admin,customer',
            ],
        ]);

        DB::beginTransaction();

        $request['role'] = isset($request['role']) ? $request['role'] : 'customer';
        $request['password'] = Hash::make($request['password']);
        $result = $this->model->create($request);
        $result->assignRole($request['role']);

        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'username' => $checkData->username,
            'email' => $checkData->email,
        ], $data);

        $request = Arr::only($data, [
            'username',
            'email',
            'password',
            'role',
        ]);

        $this->repository->validate($request, [
            'username' => [
                'min:5',
                'max:20',
                'unique:users,username,'.$checkData->id,
            ],
            'email' => [
                'email',
                'unique:users,email,'.$checkData->id,
            ],
            'password' => [
                'sometimes',
                'min:6',
                'max:20',
            ],
            'role' => [
                'in:admin,customer',
            ],
        ]);

        DB::beginTransaction();

        $roles = auth()->user()->getRoleNames()->toArray();

        if(count($roles) == 1 && in_array('customer', $roles)){
            unset($request['role']);
        }

        if(!empty($request['password'])){
            $request['password'] = Hash::make($request['password']);
        } else {
            unset($request['password']);
        }

        $checkData->update($request);

        if(!empty($request['role'])){
            $roles = $this->modelRole->whereIn('name', $request['role'])->get();
            $checkData->syncRoles($roles);
        }

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        DB::beginTransaction();

        $checkData = $this->repository->getSingleData($locale, $id);
        $checkData->roles()->detach();
        $result = $checkData->delete();

        DB::commit();

        return $result;
    }

    public function setMainAddress($locale, $data)
    {
        $request = Arr::only($data, [
            'user_id',
            'address_id',
        ]);

        $this->repository->validate($request, [
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

        $checkData = $this->repository->getSingleData($locale, $data['user_id']);
        $checkData->main_address_id = $request['address_id'];
        $checkData->save();

        DB::commit();

        return $this->repository->getSingleData($locale, $checkData->id);
    }
}

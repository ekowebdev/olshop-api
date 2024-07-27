<?php

namespace App\Http\Services;

use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;

class BaseService
{
    public static function __callStatic($method, $parameters)
    {
		$method = str_replace('to_', '', $method);
    	$thisClass = get_called_class();
		$load_service = new $thisClass;

		if (!empty($load_service->repository)) {
			if (method_exists($load_service->repository, $method)) {
				return call_user_func_array(array($load_service->repository, $method), $parameters);
			}
		}

		if (method_exists($load_service, $method)) {
			return call_user_func_array(array($load_service, $method), $parameters);
		}
    }

    public function __call($method, $parameters)
    {
    	$thisClass = get_called_class();
    	$load_service = new $thisClass;

        if (method_exists($load_service->repository, $method)) {
			return call_user_func_array(array($load_service->repository, $method), $parameters);
		}
    }

	public function validate($request, $validation, $customerMessage = [])
    {
        $validator = Validator::make($request, $validation, $customerMessage);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
    }
}

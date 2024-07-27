<?php

namespace App\Http\Repositories;

use Validator;
use App\Exceptions\ValidationException;

class BaseRepository
{
	public static function __callStatic($method, $parameters)
    {
    	if (substr($method, 0, 4) === "call") {
    		$method = lcfirst(str_replace('call', '', $method));
    		$thisClass = get_called_class();
	    	$thisClass = str_replace('Repositories', 'Services', str_replace('Repository', 'Service', $thisClass));
	    	$load_service = new $thisClass;
	        if (method_exists($load_service, $method)) {
				return call_user_func_array(array($load_service, $method), $parameters);
			}
		}
    }

	public function validate($data, $rules = [], $messages = [])
	{
		$rules = empty($rules) ? $this->model->$rules : $rules;
		$validator = Validator::make($data, $rules, $messages);
		if ($validator->fails()) throw new ValidationException($validator->errors());
		return true;
	}

	public function find($id, $model = 'model')
    {
        return $this->$model->find($id);
    }

    public function create($data, $model = 'model')
    {
        return $this->$model->create($data);
    }

	public function updateOrCreate($where = [], $data = [], $model = 'model')
    {
        return $this->$model->updateOrCreate($where, $data);
    }
}

<?php 

namespace App\Exceptions;

use Illuminate\Support\Facades\Response;

class ForbiddenException extends \Exception
{	
	public function responseJson()
	{
		return Response::json(
	        [
	            'error' => [
	                'message' => (!empty($this->message)) ? $this->message : trans('auth.not_authorize_access'), 
	                'status_code' => 403,
	                'error' => 1
	            ]
	        ], 403
		);
	}
}
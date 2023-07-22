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
	                'message' => 'Forbidden.', 
	                'status_code' => 403,
	                'error' => (!empty($this->message)) ? $this->message : trans('auth.not_authorize_access')
	            ]
	        ], 403
		);
	}
}
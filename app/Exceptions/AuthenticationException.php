<?php 

namespace App\Exceptions;

use Illuminate\Support\Facades\Response;

class AuthenticationException extends \Exception
{	
	public function responseJson()
	{
		return Response::json(
	        [
	            'error' => [
	                'message' => (!empty($this->message)) ? $this->message : trans('auth.failed'), 
					'status_code' => 401,
					'error_code' => $this->code,
	                'is_login' => 0,
	                'error' => [
	                	[
	                		(!empty($this->message)) ? $this->message : trans('auth.failed')
	                	]
	                ]
	            ]
	        ], 401
		);
	}
}
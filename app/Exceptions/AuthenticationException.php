<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Response;

class AuthenticationException extends \Exception
{
    public $taging;

    public function __construct($message = null, $taging = null, $code = 401, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->taging = $taging;
    }

	public function responseJson()
	{
		return Response::json(
	        [
	            'error' => [
	                'message' => (!empty($this->message)) ? $this->message : trans('auth.failed'),
					'status_code' => $this->code,
	                'is_login' => 0,
	                'error' => 1
	            ]
	        ], $this->code
		);
	}
}

<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Response;

class ForbiddenException extends \Exception
{
    public $taging;

    public function __construct($message = null, $taging = null, $code = 403, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->taging = $taging;
    }

	public function responseJson()
	{
		return Response::json(
	        [
	            'error' => [
	                'message' => (!empty($this->message)) ? $this->message : trans('auth.not_authorize_access'),
	                'status_code' => $this->code,
	                'error' => 1
	            ]
	        ], $this->code
		);
	}
}

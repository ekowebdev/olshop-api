<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Response;

class ValidationException extends \Exception
{
    public $taging;

    public function __construct($message = null, $taging = null , $code = 422, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->taging = $taging;
    }

	public function responseJson()
	{
		return Response::json(
	        [
	            'error' => [
	                'message' => (json_decode($this->message, true)) ? array_values(json_decode($this->message, true))[0][0] : 'Error Found.',
					'status_code' => $this->code,
					'error_tagging' => $this->taging,
	                'error' => 1
	            ]
	        ], $this->code
        );
	}
}

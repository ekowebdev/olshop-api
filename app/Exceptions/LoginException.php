<?php 

namespace App\Exceptions;

use Illuminate\Support\Facades\Response;

class LoginException extends \Exception
{	
    public $taging;

    public function __construct($message = null, $taging = null , $code = 401, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->taging = $taging;
    }

	public function responseJson()
	{
		return Response::json(
	        [
	            'error' => [
	                'message' => (!empty($this->message)) ? $this->message : trans('auth.wrong_email_or_password'), 
					'status_code' => $this->code,
					'error_tagging' => $this->taging,
	                'is_login' => 1,
	                'error' => 1
	            ]
	        ], $this->code
		);
	}
}
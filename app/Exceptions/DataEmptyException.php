<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Response;

class DataEmptyException extends \Exception
{
    public $taging;

    public function __construct($message = null, $taging = null, $code = 404, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->taging = $taging;
    }

	public function responseJson()
	{
		if(empty($this->code) && (request('search_column') || request('search'))) {
			$this->code = 204;
		}

		return Response::json(
	        [
	            'error' => [
	                'message' => (!empty($this->message)) ? $this->message : trans('error.data_not_exists'),
					'status_code' => $this->code,
	                'error' => 1
	            ]
	        ], $this->code
		);
	}
}

<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Response;

class DataEmptyException extends \Exception
{	
	public function responseJson()
	{
		if(empty($this->code) && (request('search_column') || request('search'))) {
			$this->code = 204;
		}

		return Response::json(
	        [
	            'error' => [
	                'message' => (!empty($this->message)) ? $this->message : 'Data does not exist', 
					'status_code' => 404,
	                'error' => 1
	            ]
	        ], 404
		);
	}
}
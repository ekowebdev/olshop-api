<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Response;

class SystemException extends \Exception
{
    public $taging;

    public function __construct($message = null, $taging = null, $code = 500, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->taging = $taging;
    }

    public function responseJson()
    {
        return Response::json(
            [
                'error' => [
                    'message' => (!empty($this->message)) ? $this->message : trans('error.system_error'),
                    'status_code' => $this->code,
                    'error_tagging' => $this->taging,
                    'error' => 1
                ]
            ], $this->code
        );
    }
}

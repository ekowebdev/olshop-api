<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeletedResource extends JsonResource
{
    public function toArray($request)
    {
        if(empty($this->resource))
        {
            return [
                'message' => trans('error.failed_delete_data'), 
            ];
        }

        return [
            'message' => trans('all.success_delete_data', ['attribute' => $this->resource]), 
        ];
    }

    public function with($request)
    {
        if(empty($this->resource))
        {
            return [
                'status_code' => 500,
                'error'  => 1,
            ];
        }

        return [
            'status_code' => 200,
            'error'  => 0,
        ];
    }

    public function withResponse($request, $response)
    {
        if(empty($this->resource))
        {
            $response->setStatusCode('500', trans('error.failed_delete_data'));
        }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;

class XSSProtection
{
    public function handle($request, Closure $next)
    {
        $input = $request->all();

        if (!in_array(strtolower($request->method()), ['put', 'post'])) {
            if(!empty($input['per_page']) && $input['per_page'] < 0){
                $input['per_page'] = 15;
            }

            if(!empty($input['page']) && $input['page'] < 0){
                $input['page'] = 1;
            }
            
            $request->merge($input);

            return $next($request);
        }

        if(!is_array($input)){
            $input = preg_replace(
                array('/javascript/i', '/alert/i'),
                array('#REMOVED#', '#REMOVED#'),
                $input
            );
        }

        array_walk_recursive($input, function(&$input) {
            $input = preg_replace(
                array('/javascript/i', '/alert/i'),
                array('#REMOVED#', '#REMOVED#'),
                $input
            );
            $input = htmlentities(trim(strip_tags($input)));
        });

        $request->merge($input);
        return $next($request);
    }
}
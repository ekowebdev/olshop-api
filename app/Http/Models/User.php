<?php

namespace App\Http\Models;

use Validator;
use App\Http\Models\Rating;
use App\Http\Models\Redeem;
use Illuminate\Support\Arr;
use App\Http\Models\Wishlists;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    public $table = 'users';

    public $sortableAndSearchableColumn = [];

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'email_verified_at',
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function scopeSetSortableAndSearchableColumn($query, $value = [])
	{
		$this->sortableAndSearchableColumn = $value;
	}

    public function scopeSearch($query)
	{
		$request = Request::all();

		$search        = $this->sortableAndSearchableColumn;
		$search_column = $this->sortableAndSearchableColumn;

		if(array_key_exists('search', $this->sortableAndSearchableColumn)){
			$search = $this->sortableAndSearchableColumn['search'];
		}

		if(array_key_exists('search_column', $this->sortableAndSearchableColumn)){
			$search_column = $this->sortableAndSearchableColumn['search_column'];
		}

		$this->validate($request, [
            'search_column' => [
                'required_with:search_text',
                new \App\Rules\SortableAndSearchable($search_column)
            ],
            'search_text' => ['required_with:search_column'],
        ]);

		$queryOld = $this->getSql($query);
		$thisClass = get_class($this);
		$model = new $thisClass;
		$model->sortableAndSearchableColumn = $this->sortableAndSearchableColumn;
		$query = $model->setTable(\DB::raw('('.$queryOld.') as myTable'))->whereRaw("1=1");

		if(!empty($request['search_column']) && isset($request['search_text']))
		{
			if(is_array($request['search_column']))
			{				
				foreach ($request['search_column'] as $arr_search_column => $value_search_column) {
					if($request['search_text'][$arr_search_column] != utf8_encode($request['search_text'][$arr_search_column])){
						throw new \App\Exceptions\AuthenticationException('Periksa text pencarian anda, mungkin mengandung karakter yang tidak kita ijinkan!');
					}
					$query = $this->searchOperator($query, $request['search_column'][$arr_search_column], $request['search_text'][$arr_search_column], Arr::get($request,'search_operator.'.$arr_search_column,'like'));
				}	
			}
			else
			{	
				if($request['search_text'] != utf8_encode($request['search_text'])){
					throw new \App\Exceptions\AuthenticationException('Periksa text pencarian anda, mungkin mengandung karakter yang tidak kita ijinkan!');
				}
				$query = $this->searchOperator($query, $request['search_column'], $request['search_text'], Arr::get($request,'search_operator','like'));
			}
		}

		if(isset($request['search']))
		{			
			if($request['search'] != utf8_encode($request['search'])){
				throw new \App\Exceptions\AuthenticationException('Periksa text pencarian anda, mungkin mengandung karakter yang tidak kita ijinkan!');
			}

			$query->where(function ($query) use ($search,$request) {
				foreach ($search as $key => $value) {  
                	if($value)$query->orWhere(\DB::raw($value), 'like', '%'.$request['search'].'%');
				}
            });
		}
        
        return $query;
	}

    public function searchOperator($query, $column, $text, $operator = 'like')
	{
		$search_column = $this->sortableAndSearchableColumn;

		if(array_key_exists('search_column', $this->sortableAndSearchableColumn)){
			$search_column = $this->sortableAndSearchableColumn['search_column'];
		}	

		if( $operator == 'like' )
			$query->where(\DB::raw($search_column[$column]),'like','%'.$text.'%');

		if( $operator == '=' )
			$query->where(\DB::raw($search_column[$column]),'=',$text);

		if( $operator == '>=' )
			$query->where(\DB::raw($search_column[$column]),'>=',$text);

		if( $operator == '<=' )
			$query->where(\DB::raw($search_column[$column]),'<=',$text);

		if( $operator == '>' )
			$query->where(\DB::raw($search_column[$column]),'>',$text);

		if( $operator == '<' )
			$query->where(\DB::raw($search_column[$column]),'<',$text);

		if( $operator == '<>' )
			$query->where(\DB::raw($search_column[$column]),'<>',$text);

		if( $operator == '!=' )
			$query->where(\DB::raw($search_column[$column]),'!=',$text);

		if( $operator == 'range' ){
			$explodeIn = explode(',',$text);
			$query->whereBetween(\DB::raw($search_column[$column]),$explodeIn);
		}
		
		if( $operator == 'in' ){
			$explodeIn = explode(',',$text);
			$query->whereIn(\DB::raw($search_column[$column]), $explodeIn);
		}

		if( $operator == 'notin' ){
			$explodeNotIn = explode(',',$text);
			$query->whereNotIn(\DB::raw($search_column[$column]), $explodeNotIn);
		}

		return $query;
	}

	public function getSql($model)
	{		
	    $replace = function ($sql, $bindings)
	    {
	        $needle = '?';
	        foreach ($bindings as $replace){
	            $pos = strpos($sql, $needle);
	            if ($pos !== false) {
	                if (gettype($replace) === "string") {
	                     $replace = ' "'.addslashes($replace).'" ';
	                }
	                $sql = substr_replace($sql, $replace, $pos, strlen($needle));
	            }
	        }
	        return $sql;
	    };
	    $sql = $replace($model->toSql(), $model->getBindings());

	    return $sql;
	}

    public function scopeSort($query)
	{
		$request = Request::all();

		$sort = $this->sortableAndSearchableColumn;

		if(array_key_exists('sort_column', $this->sortableAndSearchableColumn)){
			$sort = $this->sortableAndSearchableColumn['sort_column'];
		}

		if( !empty($request['sort_column']) && !empty($request['sort_type']) )
		{
			if( is_array($request['sort_column']) )
			{			
				$this->validate($request, [
					'sort_column.*' => [
						'required_with:sort_type',
						new \App\Rules\SortableAndSearchable($sort)
					],
					'sort_type.*'   => [
						'required_with:sort_column',
						\Illuminate\Validation\Rule::in(['asc','desc'])
					],
				]);
			
				foreach ($request['sort_column'] as $arr_sort_column => $value_sort_column) {
					$query->orderBy($sort[$value_sort_column],$request['sort_type'][$arr_sort_column]);
				}	
			}
			else
			{	
				$this->validate($request, [
					'sort_column' => [
						'required_with:sort_type',
						new \App\Rules\SortableAndSearchable($sort)
					],
					'sort_type'   => [
						'required_with:sort_column',
						\Illuminate\Validation\Rule::in(['asc','desc'])
					],
				]);
				
				$query->orderBy($sort[$request['sort_column']],$request['sort_type']);
			}
		}
	}

	public static function validate($data, $rules = [], $messages = [])
	{
		$rules = empty($rules) ? self::$rules : $rules;  
		if(empty($rules)) return true;
		$validator = Validator::make($data, $rules, $messages);
		if($validator->fails()) throw new ValidationException($validator->errors());
		return true;
	}

    public function scopeGetAll($query)
    {      
        return $query->select([
                    'id', 
                    'name', 
                    'username', 
                    'email',
                    'password'
                ]);
    }

	public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function redeems()
    {
        return $this->hasMany(Redeem::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}

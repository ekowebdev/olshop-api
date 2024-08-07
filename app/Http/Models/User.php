<?php

namespace App\Http\Models;

use Validator;
use App\Http\Models\Cart;
use App\Http\Models\Review;
use App\Http\Models\Order;
use Illuminate\Support\Arr;
use App\Http\Models\Address;
use App\Http\Models\Profile;
use App\Http\Models\Wishlist;
use Illuminate\Support\Carbon;
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

    protected $connection = 'mysql';
    protected $table = 'users';
	protected $appends = ['has_password'];
    protected $sortableAndSearchableColumn = [];

    protected $fillable = [
        'username',
        'email',
        'password',
        'google_id',
        'google_access_token',
		'main_address_id',
		'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

	public function getEmailVerifiedAtAttribute($value)
    {
        return ($value != null) ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

	public function getAccessToken()
    {
        return $this->accessToken;
    }

	public function getHasPasswordAttribute()
    {
        return $this->password == null ? 'no' : 'yes';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

	public function carts()
    {
        return $this->hasMany(Cart::class);
    }

	public function address()
    {
        return $this->hasMany(Address::class);
    }

	public function profile()
    {
        return $this->hasOne(Profile::class);
    }

	public function main_address()
	{
		return $this->belongsTo(Address::class, 'main_address_id');
	}

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id',
                    'username',
                    'email',
                    'password',
					'google_id',
					'google_access_token',
                    'main_address_id',
					'email_verified_at',
                ]);
    }

    public function scopeSetSortableAndSearchableColumn($query, $value = [])
	{
		$this->sortableAndSearchableColumn = $value;
	}

    public function scopeSearch($query)
	{
		$request = Request::all();

		$search       = $this->sortableAndSearchableColumn;
		$searchColumn = $this->sortableAndSearchableColumn;

		if (array_key_exists('search', $this->sortableAndSearchableColumn)) {
			$search = $this->sortableAndSearchableColumn['search'];
		}

		if (array_key_exists('search_column', $this->sortableAndSearchableColumn)) {
			$searchColumn = $this->sortableAndSearchableColumn['search_column'];
		}

		$this->validate($request, [
            'search_column' => [
                'required_with:search_text',
                new \App\Rules\SortableAndSearchable($searchColumn)
            ],
            'search_text' => ['required_with:search_column'],
        ]);

		$queryOld = $this->getSql($query);
		$thisClass = get_class($this);
		$model = new $thisClass;
		$model->sortableAndSearchableColumn = $this->sortableAndSearchableColumn;
		$query = $model->setTable(\DB::raw('('.$queryOld.') as myTable'))->whereRaw("1=1");

		if (!empty($request['search_column']) && isset($request['search_text']))
		{
			if (is_array($request['search_column']))
			{
				foreach ($request['search_column'] as $arr_search_column => $value_search_column) {
					if ($request['search_text'][$arr_search_column] != utf8_encode($request['search_text'][$arr_search_column])) {
						throw new \App\Exceptions\ApplicationException(trans('error.not_allowed_character_text'));
					}
					$query = $this->searchOperator($query, $request['search_column'][$arr_search_column], $request['search_text'][$arr_search_column], Arr::get($request,'search_operator.'.$arr_search_column,'like'));
				}
			}
			else
			{
				if ($request['search_text'] != utf8_encode($request['search_text'])) {
					throw new \App\Exceptions\ApplicationException(trans('error.not_allowed_character_text'));
				}
				$query = $this->searchOperator($query, $request['search_column'], $request['search_text'], Arr::get($request,'search_operator','like'));
			}
		}

		if (isset($request['search']))
		{
			if ($request['search'] != utf8_encode($request['search'])) {
				throw new \App\Exceptions\ApplicationException(trans('error.not_allowed_character_text'));
			}

			$query->where(function ($query) use ($search,$request) {
				foreach ($search as $key => $value) {
                	if ($value) $query->orWhere(\DB::raw($value), 'like', '%'.$request['search'].'%');
				}
            });
		}

        return $query;
	}

    public function searchOperator($query, $column, $text, $operator = 'like')
	{
		$searchColumn = $this->sortableAndSearchableColumn;

		if (array_key_exists('search_column', $this->sortableAndSearchableColumn)) {
			$searchColumn = $this->sortableAndSearchableColumn['search_column'];
		}

		if ($operator == 'like' )
			$query->where(\DB::raw($searchColumn[$column]),'like','%'.$text.'%');

		if ($operator == '=' )
			$query->where(\DB::raw($searchColumn[$column]),'=',$text);

		if ($operator == '>=' )
			$query->where(\DB::raw($searchColumn[$column]),'>=',$text);

		if ($operator == '<=' )
			$query->where(\DB::raw($searchColumn[$column]),'<=',$text);

		if ($operator == '>' )
			$query->where(\DB::raw($searchColumn[$column]),'>',$text);

		if ($operator == '<' )
			$query->where(\DB::raw($searchColumn[$column]),'<',$text);

		if ($operator == '<>' )
			$query->where(\DB::raw($searchColumn[$column]),'<>',$text);

		if ($operator == '!=' )
			$query->where(\DB::raw($searchColumn[$column]),'!=',$text);

		if ($operator == 'range' ){
			$explodeIn = explode(',',$text);
			$query->whereBetween(\DB::raw($searchColumn[$column]),$explodeIn);
		}

		if ($operator == 'in' ){
			$explodeIn = explode(',',$text);
			$query->whereIn(\DB::raw($searchColumn[$column]), $explodeIn);
		}

		if ($operator == 'notin' ){
			$explodeNotIn = explode(',',$text);
			$query->whereNotIn(\DB::raw($searchColumn[$column]), $explodeNotIn);
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

		if (array_key_exists('sort_column', $this->sortableAndSearchableColumn)){
			$sort = $this->sortableAndSearchableColumn['sort_column'];
		}

		if (!empty($request['sort_column']) && !empty($request['sort_type']) )
		{
			if (is_array($request['sort_column']) )
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
		if (empty($rules)) return true;
		$validator = Validator::make($data, $rules, $messages);
		if ($validator->fails()) throw new ValidationException($validator->errors());
		return true;
	}
}

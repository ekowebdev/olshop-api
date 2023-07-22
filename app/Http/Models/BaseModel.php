<?php

namespace App\Http\Models;

use Validator;
use Illuminate\Support\Arr;
use App\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class BaseModel extends Model
{
	protected $guarded = [];
	
	protected $soft_delete = false;

	protected $with_log	= false;
	
	protected $casts = ['created_at' => 'string'];

	protected static $rules = [];

	/**
	 * [$sortableAndSearchableColumn description]
	 * @var array
	 */
	public $sortableAndSearchableColumn = [];

	/**
	 * relationColumn variable
	 *
	 * @var array
	 */
	public $relationColumn = [];

	/**
	 * set All Model without timestamps
	 * @var boolean
	 */
	public $timestamps = true;

	/**
	 * [setSortableAndSearchableColumn description]
	 * @param array $value [description]
	 */
	public function scopeSetSortableAndSearchableColumn($query, $value=[])
	{
		$this->sortableAndSearchableColumn = $value;
	}

	/**
	 * set relationColumn function
	 *
	 * @param [type] $query
	 * @param array $value
	 * @return void
	 */
	public function scopeSetRelationColumn($query, $value=[])
	{		
		$this->relationColumn = $value;
	}

	/**
	 * [FunctionName description]
	 * @param string $value [description]
	 */

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
			#Handle error unicode
			#SQLSTATE[HY000]: General error: 1267 Illegal mix of collations (latin1_swedish_ci,IMPLICIT) and (utf8mb4_unicode_ci,COERCIBLE) 
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

	/**
	 * [searchOperator description]
	 * @param  [type] $query    [description]
	 * @param  [type] $column   [description]
	 * @param  [type] $text     [description]
	 * @param  string $operator [description]
	 * @return [type]           [description]
	 */
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

	/**
	 * [getSql description]
	 * @param  [type] $model [description]
	 * @return [type]        [description]
	 */
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

	/**
	 * distinct function
	 *
	 * @param [type] $query
	 * @return void
	 */
	public function scopeDistinct($query,$data=null)
	{
		$request = Request::all();		
		
		$this->validate($request, [
            'distinct_column' => [
                'filled',
                new \App\Rules\SortableAndSearchable($this->sortableAndSearchableColumn+$this->relationColumn),
            ],
		]);
		
		if(!empty($data)) {
			$request['distinct_column'] = $data;
		}

		if( !empty($request['distinct_column']) )
		{
			if( is_array($request['distinct_column']) )
			{
				$colsDistinct = implode(',',$request['distinct_column']);
				$query->select(\DB::raw('distinct '.$colsDistinct));
			}
			else
			{
				$query->select(\DB::raw('distinct '.$request['distinct_column']));
			}
		}
	}

	/**
	 * [FunctionName description]
	 * @param string $value [description]
	 */
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

	/**
	 * [scopeUseIndex description]
	 * @param  [type] $query [description]
	 * @return [type]        [description]
	 */
	public function scopeIndex($query, $index_name, $type = 'FORCE')
	{
		$thisClass = get_class($this);
		$model = new $thisClass;
		return $query->from(\DB::raw(''.$model->getTable().' '.$type.' INDEX ('.$index_name.')'));
	}

	/**
	 * [validate description]
	 * @param  [type] $data     [description]
	 * @param  array  $rules    [description]
	 * @param  array  $messages [description]
	 * @return [type]           [description]
	 */
	public static function validate($data, $rules = [], $messages = [])
	{
		$rules = empty($rules) ? self::$rules : $rules;  
		if(empty($rules)) return true;
		$validator = Validator::make($data, $rules, $messages);
		if($validator->fails()) throw new ValidationException($validator->errors());
		return true;
	}

	/**
     * [scopeGetAll description]
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function scopeGetAll($query)
    {
        return $query;
    }

    /**
     * [getModifiedTimeAttribute description]
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    public function getModifiedTimeAttribute($date)
	{
	    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', (is_null($date) ? '00-00-00 00:00:00' : $date) )->format('Y-m-d H:i:s');
	}

	/**
	 * check attribute function
	 *
	 * @param [type] $attr
	 * @return boolean
	 */
	public function hasAttribute($attr)
	{
		return array_key_exists($attr, $this->attributes);
	}

    /**
     * [getKeyPrimaryTabel description]
     * @return [type] [description]
     */
    public function getKeyPrimaryTableAttribute()
    {
		$thisClass = get_class($this);
		$model = new $thisClass;

        return $model->getTable().'.'.$model->getKeyName(); 
    }
}

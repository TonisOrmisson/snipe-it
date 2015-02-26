<?php

class Model extends Elegant
{
    use SoftDeletingTrait;
    protected $dates = ['deleted_at'];
    protected $table = 'models';

    // Declare the rules for the form validation
    protected $rules = array(
        'name'   		=> 'required|alpha_space|min:3|max:255|unique:models,name,{id}',
        'modelno'   		=> 'alpha_space|min:1|max:255|unique:models,modelno,{id}',
        'category_id'   	=> 'required|integer',
        'manufacturer_id'   => 'required|integer',
        'eol'   => 'required|integer:min:0|max:240',
        'user_id' => 'integer',
    );

    public function assets()
    {
        return $this->hasMany('Asset', 'model_id');
    }
    
    public function trashedAssets()
    {
        return $this->hasMany('Asset', 'model_id')->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo('Category', 'category_id');
    }

    public function depreciation()
    {
        return $this->belongsTo('Depreciation','depreciation_id');
    }

    public function adminuser()
    {
        return $this->belongsTo('User','user_id');
    }

    public function manufacturer()
    {
        return $this->belongsTo('Manufacturer','manufacturer_id');
    }
    
    /**
	-----------------------------------------------
	BEGIN QUERY SCOPES
	-----------------------------------------------
	**/
    
    /**
	* Query builder scope for Deleted assets
	*
	* @param  Illuminate\Database\Query\Builder  $query  Query builder instance
	* @return Illuminate\Database\Query\Builder          Modified query builder
	*/

	public function scopeDeleted($query)
	{
		return $query->whereNotNull('deleted_at');
	}

}

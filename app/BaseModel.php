<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    // use \Venturecraft\Revisionable\RevisionableTrait;
    // protected $revisionEnabled = true;
    // protected $revisionCleanup = true; 
    // protected $historyLimit = 500; 
    
    // protected $guarded = ['created_at'];
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        // static::addGlobalScope('siteentry', function(Builder $builder){
        //     $builder->where('synched', '!=', 3);
        // });
    }
    

    protected function date_modifier($value)
    {
    	if($value) return date('d-M-Y', strtotime($value));

    	return $value;
    }

    public function my_date_format($value)
    {
        if($this->$value) return date('d-M-Y', strtotime($this->$value));

        return '';
    }

    public function my_time_format($value)
    {
        if($this->$value) return date('d-M-Y H:i:s', strtotime($this->$value));

        return '';
    }

    public function my_string_format($value, $default='0')
    {
        if($this->$value) return (string) $this->$value;
        return $default;
    }

    public function output_array($value)
    {
        return eval('return ' . $this->value . ';');
    }

    public function pre_update()
    {
        if($this->synched == 1 && $this->isDirty()) $this->synched = 2;
        $this->save();
    }

    public function pre_delete()
    {
        if($this->synched == 1){
            $this->synched = 3;
        }else{
            $this->delete();
        }
        
    }
}

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
    // protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        // static::addGlobalScope('siteentry', function(Builder $builder){
        //     $builder->where('synched', '!=', 3);
        // });
    }

    public function getAgeInDaysAttribute()
    {
        $now = \Carbon\Carbon::now();
        return $now->diffInDays($this->created_at);
    }

    public function getAgeInWeeksAttribute()
    {
        $now = \Carbon\Carbon::now();
        return $now->diffInWeeks($this->created_at);
    }

    public function getAgeInMonthsAttribute()
    {
        $now = \Carbon\Carbon::now();
        return $now->diffInMonths($this->created_at);
    }

    public function getHyperlinkAttribute()
    {
        $user = auth()->user();
        $c = get_class($this);
        $c = strtolower($c);
        $c = str_replace_first('app\\', '', $c);

        $url = url($c . '/' . $this->id);
        if(str_contains($c, 'sample')) $url = url($c . '/runs/' . $this->id);
        if(str_contains($c, 'worksheet')) $url = url($c . '/approve/' . $this->id);

        if(str_contains($c, ['worksheet', 'sample']) && (!$user || ($user && $user->user_type_id == 5))) return $this->id;

        $text = $this->id;

        if(str_contains($c, 'patient')) $text = $this->patient;
        if(str_contains($c, 'sample')) $text = "View Runs";

        $full_link = "<a href='{$url}' target='_blank'> {$text} </a>";

        return $full_link;
    }

    public function get_link($attr)
    {
        $user = auth()->user();
        $c = get_class($this);
        $c = strtolower($c);
        $c = str_replace_first('app\\', '', $c);

        $pre = '';
        if(str_contains($c, 'viral')) $pre = 'viral';
        if(str_contains($c, 'dr')) $pre = 'dr_';
        $user = auth()->user();

        if(str_contains($attr, 'extraction')) $url = url('dr_extraction_worksheet/gel_documentation/' . $this->$attr);
        else if(str_contains($attr, 'worksheet')) $url = url($pre . 'worksheet/approve/' . $this->$attr);
        else if(str_contains($attr, 'sample') || (str_contains($c, 'sample') && $attr == 'id')) $url = url($pre . 'sample/runs/' . $this->$attr);
        else{
            $a = explode('_', $attr);
            $url = url($pre . $a[0] . '/' . $this->$attr);
            // if(str_contains($c, 'patient')) $url = url($pre . $a[0] . '/' . $this->patient_id);
        }

        if($attr == 'id' && (!$user || ($user && $user->user_type_id == 5))) return null;

        if(str_contains($attr, ['worksheet', 'sample']) && (!$user || ($user && $user->user_type_id == 5))) return $this->$attr;

        $text = $this->$attr;

        if(str_contains($c, 'patient')) $text = $this->patient;

        $full_link = "<a href='{$url}' target='_blank'> {$text} </a>";

        return $full_link;
    }

    public function get_prop_name($coll, $attr, $attr2='name')
    {
        if(!$this->$attr) return '';
        foreach ($coll as $value) {
            if($value->id == $this->$attr) return $value->$attr2;
        }
        return '';
    }
    

    protected function date_modifier($value)
    {
    	if($value) return date('d-M-Y', strtotime($value));
    	return $value;
    }

    public function my_date_format($value, $format='d-M-Y')
    {
        if($this->$value) return date($format, strtotime($this->$value));
        return '';
    }

    public function my_time_format($value)
    {
        if($this->$value) return date('d-M-Y H:i:s', strtotime($this->$value));
        return '';
    }

    public function my_boolean_format($value)
    {
        if($this->$value) return "Yes";
        return 'No';
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

    public function edarp()
    {
        if(!$this->synched){
            $this->synched = 5;
            $this->save();
        }
        else{
            $this->pre_update();
        }
    }
}

<?php

namespace App;

use App\BaseModel;

class Facility extends BaseModel
{
    //
    protected $table = "facilitys";
    public $timestamps = false;


    public function facility_contact()
    {
        return $this->hasOne('App\FacilityContact');
    }

    public function facility_user()
    {
        return $this->hasOne('App\User');
    }

    public function scopeLocate($query, $mfl)
    {
        return $query->where("facilitycode", $mfl);
    }

    /**
     * Get the facility's branch location
     *
     * @return string
     */
    public function getBranchLocationAttribute()
    {
        if($this->facility_contact->G4Sbranchname && $this->facility_contact->G4Slocation){
        	return $this->facility_contact->G4Sbranchname . ' , ' . $this->facility_contact->G4Slocation;
		}
		else if($this->facility_contact->G4Sbranchname && !$this->facility_contact->G4Slocation){
			return $this->facility_contact->G4Sbranchname;
		}
		else if(!$this->facility_contact->G4Sbranchname && $this->facility_contact->G4Slocation){
        	return $this->facility_contact->G4Slocation;
		}
		else{
			return null;
		}
    }

    public function getBranchPhonesAttribute()
    {
        if($this->facility_contact->G4Sphone1 != '' && $this->facility_contact->G4Sphone2 != '' && $this->facility_contact->G4Sphone3 != ''){
        	return $this->facility_contact->G4Sphone1 . ' / ' . $this->facility_contact->G4Sphone2 . ' / ' . $this->facility_contact->G4Sphone3;
		}
		else if($this->facility_contact->G4Sphone1 != '' && $this->facility_contact->G4Sphone2 != '' && $this->facility_contact->G4Sphone3 == ''){
        	return $this->facility_contact->G4Sphone1 . ' / ' . $this->facility_contact->G4Sphone2;
		}
		else if($this->facility_contact->G4Sphone1 != '' && $this->facility_contact->G4Sphone2 == '' && $this->facility_contact->G4Sphone3 != ''){
        	return $this->facility_contact->G4Sphone1 . ' / ' . $this->facility_contact->G4Sphone3;
		}
		else if($this->facility_contact->G4Sphone1 != '' && $this->facility_contact->G4Sphone2 == '' && $this->facility_contact->G4Sphone3 == ''){
        	return $this->facility_contact->G4Sphone1;
		}
		else{
			return null;
		}
    }

    public function getContactsAttribute()
    {
        if($this->facility_contact->contacttelephone != '' && $this->facility_contact->contacttelephone2 != ''){
        	return $this->facility_contact->contacttelephone . ' / ' . $this->facility_contact->contacttelephone2;
		}
		else if($this->facility_contact->contacttelephone != '' && $this->facility_contact->contacttelephone2 == ''){
        	return $this->facility_contact->contacttelephone;
		}
		else if($this->facility_contact->contacttelephone == '' && $this->facility_contact->contacttelephone2 != ''){
        	return $this->facility_contact->contacttelephone2;
		}
		else{
			return null;
		}
    }

    public function getFacilityContactsAttribute()
    {
        if($this->facility_contact->telephone != '' && $this->facility_contact->telephone2 != ''){
        	return $this->facility_contact->telephone . ' / ' . $this->facility_contact->telephone2;
		}
		else if($this->facility_contact->telephone != '' && $this->facility_contact->telephone2 == ''){
        	return $this->facility_contact->telephone;
		}
		else if($this->facility_contact->telephone == '' && $this->facility_contact->telephone2 != ''){
        	return $this->facility_contact->telephone2;
		}
		else{
			return null;
		}
    }

    public function getEmailAttribute()
    {
        return $this->facility_contact->email ?? '';
    }
}

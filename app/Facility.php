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

    public function getEmailStringAttribute()
    {
        if($this->facility_contact->email != '' && $this->facility_contact->ContactEmail != ''){
            return $this->facility_contact->email . ' / ' . $this->facility_contact->ContactEmail;
        }
        else if($this->facility_contact->email != '' && $this->facility_contact->ContactEmail == ''){
            return $this->facility_contact->email;
        }
        else if($this->facility_contact->email == '' && $this->facility_contact->ContactEmail != ''){
            return $this->facility_contact->ContactEmail;
        }
        else{
            return null;
        }
    }

    public function getTelephoneStringAttribute()
    {
        if($this->facility_contact->telephone != '' && $this->facility_contact->contacttelephone != ''){
            return $this->facility_contact->telephone . ' / ' . $this->facility_contact->contacttelephone;
        }
        else if($this->facility_contact->telephone != '' && $this->facility_contact->contacttelephone == ''){
            return $this->facility_contact->telephone;
        }
        else if($this->facility_contact->telephone == '' && $this->facility_contact->contacttelephone != ''){
            return $this->facility_contact->contacttelephone;
        }
        else{
            return null;
        }
    }

    public function getEmailArrayAttribute()
    {
        $emails = [];
        if($this->email) $emails[] = $this->email;
        if($this->contact_email) $emails[] = $this->contact_email;
        $f = ['dmltemail', 'dtlcemail', 'subcountyemail', 'countyemail', 'partneremail', 'partnerlabmail', 'partnerpointmail'];

        foreach ($f as $val) {
            if($this->$val && $this->$val != '') $emails[] = $this->$val;
        }
        return $emails;
    }

    public function getEmailAttribute()
    {
        return $this->facility_contact->email ?? $this->email ?? null;
    }

    public function getTelephoneAttribute()
    {
        return $this->facility_contact->telephone ?? $this->telephone ?? null;
    }

    public function getTelephone2Attribute()
    {
        return $this->facility_contact->telephone2 ?? $this->telephone2 ?? null;
    }

    public function getContactpersonAttribute()
    {
        return $this->facility_contact->contactperson ?? $this->contactperson ?? null;
    }

    public function getContacttelephoneAttribute()
    {
        return $this->facility_contact->contacttelephone ?? $this->contacttelephone ?? null;
    }

    public function getContacttelephone2Attribute()
    {
        return $this->facility_contact->contacttelephone2 ?? $this->contacttelephone2 ?? null;
    }

    public function getContactEmailAttribute()
    {
        return $this->facility_contact->ContactEmail ?? $this->ContactEmail ?? null;
    }
}

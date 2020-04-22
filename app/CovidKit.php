<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidKit extends BaseModel
{
    public function computekitsUsed($tests)
    {
    	if ($tests == 0 || $this->calculated_pack_size == NULL)
    		return 0;
    	
    	return (int)round(($tests + (($tests/94) * 2))/$this->calculated_pack_size);
    	// return $tests;
    }

    public function beginingbalance()
    {
    	$balance = 0;
    	return $balance;
    }
}

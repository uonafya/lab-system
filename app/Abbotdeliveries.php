<?php

namespace App;

use App\BaseModel;

class Abbotdeliveries extends BaseModel
{
    // protected $fillable = ['testtype','lab','quarter','year','source','labfrom','qualkitlotno','calibrationlotno','controllotno','bufferlotno','preparationlotno','qualkitexpiry','calibrationexpiry','controlexpiry','bufferexpiry','preparationexpiry','qualkitreceived','calibrationreceived','controlreceived','bufferreceived','preparationreceived','adhesivereceived','deepplatereceived','mixtubereceived','reactionvesselsreceived','reagentreceived','reactionplatereceived','1000disposablereceived','200disposablereceived','qualkitdamaged','calibrationdamaged','controldamaged','bufferdamaged','preparationdamaged','adhesivedamaged','deepplatedamaged','mixtubedamaged','reactionvesselsdamaged','reagentdamaged','reactionplatedamaged','1000disposabledamaged','200disposabledamaged','receivedby','datereceived','status','enteredby','dateentered','flag','datesynchronized'];

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }

    public function lab_from()
    {
        return $this->belongsTo('App\Lab', 'labfrom');
    }

    public function receiver()
    {
        return $this->belongsTo('App\User', 'receivedby');
    }

    public function enterer()
    {
        return $this->belongsTo('App\User', 'enteredby');
    }
    
}

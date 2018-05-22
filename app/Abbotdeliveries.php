<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Abbotdeliveries extends Model
{
    protected $fillable = ['testtype','lab','quarter','year','source','labfrom','qualkitlotno','calibrationlotno','controllotno','bufferlotno','preparationlotno','qualkitexpiry','calibrationexpiry','controlexpiry','bufferexpiry','preparationexpiry','qualkitreceived','calibrationreceived','controlreceived','bufferreceived','preparationreceived','adhesivereceived','deepplatereceived','mixtubereceived','reactionvesselsreceived','reagentreceived','reactionplatereceived','1000disposablereceived','200disposablereceived','qualkitdamaged','calibrationdamaged','controldamaged','bufferdamaged','preparationdamaged','adhesivedamaged','deepplatedamaged','mixtubedamaged','reactionvesselsdamaged','reagentdamaged','reactionplatedamaged','1000disposabledamaged','200disposabledamaged','receivedby','datereceived','status','enteredby','dateentered','flag','datesynchronized'];
    
}

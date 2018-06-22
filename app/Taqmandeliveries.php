<?php

namespace App;

use App\BaseModel;

class Taqmandeliveries extends BaseModel
{
    // protected $fillable = ['testtype','lab','quarter','year','source','labfrom','kitlotno','expirydate','qualkitreceived','spexagentreceived','ampinputreceived','ampflaplessreceived','ampktipsreceived','ampwashreceived','ktubesreceived','consumablesreceived','qualkitdamaged','spexagentdamaged','ampinputdamaged','ampflaplessdamaged','ampktipsdamaged','ampwashdamaged','ktubesdamaged','consumablesdamaged','receivedby','datereceived','status','enteredby','dateentered','flag','datesynchronized'];



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

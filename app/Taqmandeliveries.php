<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Taqmandeliveries extends Model
{
    protected $fillable = ['testtype','lab','quarter','year','source','labfrom','kitlotno','expirydate','qualkitreceived','spexagentreceived','ampinputreceived','ampflaplessreceived','ampktipsreceived','ampwashreceived','ktubesreceived','consumablesreceived','qualkitdamaged','spexagentdamaged','ampinputdamaged','ampflaplessdamaged','ampktipsdamaged','ampwashdamaged','ktubesdamaged','consumablesdamaged','receivedby','datereceived','status','enteredby','dateentered','flag','datesynchronized'];
}

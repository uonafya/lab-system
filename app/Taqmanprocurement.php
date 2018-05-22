<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Taqmanprocurement extends Model
{
    protected $fillable = ['month','year','testtype','received','tests','datesubmitted','submittedBy','lab_id','datesynchronized','comments','issuedcomments','approve','disapproverreason','endingqualkit','endingspexagent','endingampinput','endingampflapless','endingampktips','endingampwash','endingktubes','endingconsumables','wastedqualkit','wastedspexagent','wastedampinput','wastedampflapless','wastedampktips','wastedampwash','wastedktubes','wastedconsumables','issuedqualkit','issuedspexagent','issuedampinput','issuedampflapless','issuedampktips','issuedampwash','issuedktubes','issuedconsumables','requestqualkit','requestspexagent','requestampinput','requestampflapless','requestampktips','requestampwash','requestktubes','requestconsumables','posqualkit','posspexagent','posampinput','posampflapless','posampktips','posampwash','posktubes','posconsumables'];
}

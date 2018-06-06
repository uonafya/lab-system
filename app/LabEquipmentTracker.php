<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabEquipmentTracker extends Model
{
    protected $fillable = ['month','year','lab_id','equipment_id','datesubmitted','submittedBy','dateemailsent','datebrokendown','datereported','datefixed','downtime','samplesnorun','failedruns','reagentswasted','breakdownreason','othercomments'];
}

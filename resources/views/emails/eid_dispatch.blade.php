<p>

	Hello {{ $batch->facility->name }},

	<br />
	<br />


	Batch No {{ $batch->id }} with {{ $batch->sample->count() }} samples that were received on {{ $batch->my_date_format('datereceived') }}  have been processed and results have been dispatched. 
	<br />

	@isset($batch->facility->G4Slocation )
		The samples results can be collected at your nearest G4S branch in {{$batch->facility->G4Slocation}}.
	@endisset

	<br />

	------------------------------------------------------------------------------------------ 

	<br />

	Click this link to access and download the results as well as view your historical batches:

	<br />

	<a href="https://eiddash.nascop.org"></a> 

	<br />

	------------------------------------------------------------------------------------------

	<br />

	The Viral Load Test is now available in all EID testing sites. Samples can be collected in DBS form and shipped using the A/C C00339.  Call the official EID lines for more information.

	<br />  

	This email was automatically generated. Please do not respond to this or it will be ignored.

	<br />

	Regards, <br />
	{{ $batch->lab->labname }}
	{{ $batch->lab->name }} 
	{{ $batch->lab->lablocation }}
	{{ $batch->lab->labtel1 }} 
	{{ $batch->lab->labtel2 }} 
	{{ $batch->lab->email }} 


</p>
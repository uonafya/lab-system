<p>

	Hello {{ $facility->name }},

	<br />
	<br />


	Batch No {{ $batch->id }} with {{ $batch->sample->count() }} samples that were received on {{ $batch->datereceived->toFormattedDateString() }}  have been processed and results have been dispatched. 
	<br />

	@isset($g4sbranchlocation)
		The samples results can be collected at your nearest G4S branch in {{$g4sbranchlocation}}.
	@endisset

	<br />
	
	Please confirm that this email address

	@isset($g4sbranchlocation)
		and the G4S branch above 
	@endisset

	 is correct by responding receipt of this email to the following email address: {{ config('mail.from.address') }} 

	<br />

	------------------------------------------------------------------------------------------ 

	<br />

	Click this link to access and download the results as well as view your historical batches:

	<br />

	{{$site_url}} 

	<br />

	------------------------------------------------------------------------------------------

	<br />

	The Viral Load Test is now available in all EID testing sites. Samples can be collected in DBS form and shipped using the A/C C00339.  Call the official EID lines for more information.

	<br />  

	This email was automatically generated. Please do not respond to this or it will be ignored.

	<br />

	Regards, <br />
	-- <br />
	KEMRI CIPDCR -Alupe <br />
	Busia - Malaba Rd, Busia <br />
	Email: eid-alupe@googlegroups.com <br />
	Phone:0726156679 <br />

</p>
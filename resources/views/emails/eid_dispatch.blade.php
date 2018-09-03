<p>

	Hello {{ $batch->facility->name }},

	<br />
	<br />

	Please find attached {{ $type }} Results for Batch No {{ $batch->id }} with {{ $batch->sample->count() }} samples that were received on {{ $batch->my_date_format('datereceived') }}. 
	
	<br />

	Any pending results are still being processed and will be sent to you once they are ready.

	<br />

	You can also access this results and any other results via the link below on NASCOP
	
	<br />

	------------------------------------------------------------------------------------------ 

	<br />

	<a href="https://eiddash.nascop.org">NASCOP</a> 

	<br />

	------------------------------------------------------------------------------------------

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
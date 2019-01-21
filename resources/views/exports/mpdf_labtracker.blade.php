
<!DOCTYPE html>
<html>
<head>

	<style type="text/css">
		body {
			font-weight: 1px;
		}

		table {
			border-collapse: collapse;
			margin-bottom: .5em;
		}

		table, th, td {
			border: 1px solid black;
			border-style: solid;
     		font-size: 8px;
		}

		h5 {
			margin-top: 6px;
		    margin-bottom: 6px;
		}

		p {
			margin-top: 2px;
     		font-size: 8px;
		}
		* {
			font-size: 8px;
		}
	</style>
</head>
<body>

		<table class="table" border="0" style="border: 0px; width: 100%;">
			<tr>
				<td colspan="7" align="center">
					<img src="http://lab-2.test.nascop.org/img/naslogo.jpg" alt="NASCOP">
				</td>
			</tr>
			<tr>
				<td colspan="9" align="center">
					<h5>{{ $lab->name }}</h5>
				</td>
			</tr>
		</table>

		<br />

		@forelse($data->performance as $performance)
            <table class="table table-striped table-bordered table-hover data-table" style="font-size: 10px;margin-top: 1em;">
                <tbody>
                	<tr>
                        <th colspan="7">
                    	@if($performance->testtype == 1)
	                    	EID
	                    @else
	                    	VL - (@if($performance->sampletype == 1) Plasma @else DBS @endif)
	                    @endif
                        </th>
                    </tr>
                    <tr>
                        <th>Period</th>
                        <th>#Received Samples</th>
                        <th>#Rejected Samples</th>
                        <th># Logged in System</th>
                        <th># NOT Logged in System</th>
                        <th># Tested</th>
                        <th>Reasons for Backlog</th>
                    </tr>
                    <tr>
                        <th>
                            {{ date("F", mktime(null, null, null, $performance->month)) }}, {{ $performance->year }}
                        </th>
                        <td>
                            {{ $performance->received }}
                        </td>
                        <td>
                            {{ $performance->rejected }}
                        </td>
                        <td>
                            {{ $performance->loggedin }}
                        </td>
                        <td>
                            {{ $performance->notlogged }}
                        </td>
                        <td>
                        	@if($performance->testtype == 1)
		                    	{{ $data->eidcount }}
		                    @elseif($performance->testtype == 2)
		                    	@if($performance->sampletype == 1)
		                    		{{ $data->vlplasmacount }}
		                    	@else 
		                    		{{ $data->vldbscount }}
		                    	@endif
		                    @endif
                        </td>
                        <td>
                            {{ $performance->reasonforbacklog }}
                        </td>
                    </tr>
                    <tr>
                    	<td colspan="7">
                    		<strong>Rejected Reasons: </strong><br>
                    	@if($performance->testtype == 1)
                    		@foreach($data->eidrejected as $key => $rejected)
                        		{{ $rejected->name }}&nbsp;;&nbsp;
	                    	@endforeach
	                    @elseif($performance->testtype == 2)
	                    	@if($performance->sampletype == 1)
	                    		@foreach($data->vlplasmarejected as $key => $rejected)
	                        		{{ $rejected->name }}&nbsp;;&nbsp;
		                    	@endforeach
	                    	@else 
	                    		@foreach($data->vldbsrejected as $key => $rejected)
	                        		{{ $rejected->name }}&nbsp;;&nbsp;
		                    	@endforeach
	                    	@endif
	                    @endif
                    	</td>
                    </tr>
                </tbody>
            </table>
        @empty
        	
        @endforelse
		<br />
		<table style="font-size: 10px;margin-top: 1em; width: 100%;">
            <tbody>
            	<tr>
            		<th colspan="10" align="center">EQUIPMENT LOG</th>
            	</tr>
            	 <tr>
                    <th>#</th>
                    <th>Equipment</th>
                    <th>Date of breakdown</th>
                    <th>Date Reported</th>
                    <th>Date Fixed</th>
                    <th>Downtime (Days)</th>
                    <th># of samples Not Run</th>
                    <th># of failed Runs</th>
                    <th># of reagents wasted</th>
                    <th>Breakdown Reason</th>
                </tr>
            @forelse($data->equipments as $key => $equipment)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $equipment->equipment->name ?? '' }}</td>
                    <td>@isset($equipment->datebrokendown){{ date('d M, Y', strtotime($equipment->datebrokendown)) }} @endisset</td>
                    <td>@isset($equipment->datereported){{ date('d M, Y', strtotime($equipment->datereported)) }} @endisset</td>
                    <td>@isset($equipment->datefixed){{ date('d M, Y', strtotime($equipment->datefixed)) }} @endisset</td>
                    <td>{{ $equipment->downtime ?? '' }}</td>
                    <td>{{ $equipment->samplesnorun ?? '' }}</td>
                    <td>{{ $equipment->failedruns ?? '' }}</td>
                    <td>{{ $equipment->reagentswasted ?? '' }}</td>
                    <td>{{ $equipment->breakdownreason ?? '' }}</td>
                </tr>
            @empty
            	<tr><td colspan="10"><center>No Data Available</center></td></tr>
            @endforelse
            </tbody>
        </table>
</body>
</html>


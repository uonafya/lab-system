<div class="panel-body">
    <div class="alert alert-warning">
        <!-- Please select the parameters from the options below to generate the Submitted Kits Consumption query. -->
    </div>
    <div class="table-responsive" style="margin-top: 2em;">
        <table class="table table-striped table-bordered table-hover" >
            <thead>
                <tr>
                    <th>#</th>
                    <th>Allocation Period</th>
                    <th>Test Type</th>
                    <th>Pending Approval</th>
                    <th>Approved</th>
                    <th>Rejected</th>
                    <th>Tasks</th>
                </tr>
            </thead>
            <tbody> 
            @foreach($data['allocations'] as $key => $allocation)
                <tr>
                    <td> {{ $key + 1 }} </td>
                    <td> 
                        {{ date("F", mktime(null, null, null, $allocation->month)) }}, 
                        {{ $allocation->year }}
                    </td>
                    <td>
                    @if($allocation->testtype == 1)
                        EID
                    @elseif($allocation->testtype == 2)
                        VL
                    @else 
                        CONSUMABLES
                    @endif
                    </td>
                    <td><center><span class="label label-{{ $data['badge']($allocation->pending, 1) }}">{{ $allocation->pending }}</span></center></td>
                    <td><center><span class="label label-{{ $data['badge']($allocation->approved, 2) }}">{{ $allocation->approved }}</span></center></td>
                    <td><center><span class="label label-{{ $data['badge']($allocation->rejected, 3) }}">{{ $allocation->rejected }}</span></center></td>
                    <td>
                        <a href="{{ url('report/allocation/'.$allocation->testtype.'/'.$allocation->year.'/'.$allocation->month) }}" class="btn btn-default">
                            View
                        </a>
                        @if($allocation->rejected > 0)
                         | 
                        <a href="{{ url('report/allocation/'.$allocation->testtype.'/'.$allocation->year.'/'.$allocation->month.'/1') }}" class="btn btn-warning">
                            Update Rejected
                        </a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
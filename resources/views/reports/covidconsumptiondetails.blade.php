@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
<style type="text/css">
    .spacing-div-form {
        margin-top: 15px;
    }
</style>
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-info">
                COVID-19 Consumption report for week: {{$consumption->start_of_week}} - {{$consumption->end_of_week}}
            </div>
            <div class="hpanel">
                <div class="panel-body table-responsive" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                	<table class="table table-striped table-bordered table-hover data-table-modified" style="font-size: 10px;margin-top: 1em;">
                		<thead>
                			<tr>
                				<th>#</th>
                                <th>Material No</th>
                                <th>Product Description</th>
                                <th>Begining Balance</th>
                                <th>Received</th>
                                <th>Used</th>
                                <th>Positive Adjustment</th>
                                <th>Negative Adjustment</th>
                                <th>Losses/Wastage</th>
                                <th>Ending Balance</th>
                                <th>Requested</th>
                			</tr>
                		</thead>
                		<tbody>
                        @foreach($consumption->details as $key => $detail)
                            <tr>
                				<td>{{ $key+1 }}</td>
                                <td>{{ $detail->kit->material_no ?? '' }}</td>
                                <td>{{ $detail->kit->product_description ?? '' }}</td>
                                <td>{{ $detail->begining_balance ?? '' }}</td>
                                <td>{{ $detail->received ?? '' }}</td>
                                <td>{{ $detail->kits_used ?? '' }}</td>
                                <td>{{ $detail->positive ?? '' }}</td>
                                <td>{{ $detail->negative ?? '' }}</td>
                                <td>{{ $detail->wastage ?? '' }}</td>
                                <td>{{ $detail->ending ?? '' }}</td>
                                <td>{{ $detail->requested ?? '' }}</td>
                			</tr>
            			@endforeach
                		</tbody>
                	</table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts') 

    @component('/tables/scripts')
        
    @endcomponent
    <script type="text/javascript">
        $(document).ready(function(){
            $('.data-table-modified').dataTable({
                dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
                lengthMenu: [ [50, -1], [50, "All"] ],                
                buttons : [
                {
                  text:  'Export to CSV',
                  extend: 'csvHtml5',
                  title: "{{ $consumption->lab->labdesc }} COVID Consumption {{date('Ymd', strtotime($consumption->start_of_week))}} to {{date('Ymd', strtotime($consumption->end_of_week))}}"
                },
                {
                  text:  'Export to Excel',
                  extend: 'excelHtml5',
                  title: "{{ $consumption->lab->labdesc }} COVID Consumption {{date('Ymd', strtotime($consumption->start_of_week))}} to {{date('Ymd', strtotime($consumption->end_of_week))}}"
                },
                {
                  text:  'Export to PDF',
                  extend: 'pdfHtml5',
                  title: "{{ $consumption->lab->labdesc }} COVID Consumption {{date('Ymd', strtotime($consumption->start_of_week))}} to {{date('Ymd', strtotime($consumption->end_of_week))}}"
                }
              ]
                
            });
        });
    </script>
@endsection
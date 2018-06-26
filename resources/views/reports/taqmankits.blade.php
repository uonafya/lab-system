@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
<style type="text/css">
    .spacing-div-form {
        margin-top: 15px;
    }
</style>
@php
    $viraltestingSys = '';
    $eidtestingSys = '';
    if(Session('testingSystem') == 'Viralload') {
        $viraltestingSys = 'checked';
    } else {
        $eidtestingSys = 'checked';
    }
@endphp
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                    @forelse($data->kits as $kits)
                    <table class="table table-striped table-bordered table-hover" style="font-size: 10px;margin-top: 1em;">
                        <thead>               
                            <tr>
                                <th></th>
                                <th>HIV Quantitative Test Kits</th>
                                <th>SPEX Agent</th>
                                <th>Ampliprep Input s-tube</th>
                                <th>Ampliprep flapless SPU</th>
                                <th>Ampliprep K-tips</th>
                                <th>Ampliprep Wash Reagent</th>
                                <th>TAQMAN K-tubes</th>
                                <th>CAP/CTM Consumable Bundles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $receivedsuffix = 'received';
                                $damagedsuffix = 'damaged';
                            @endphp
                            <tr>
                                <td>Received</td>
                                @foreach($data->taqmandata as $key => $taqmandata)
                                    @php
                                        $prefix = $taqmandata['alias'].$receivedsuffix;
                                    @endphp
                                    <td>{{ $kits->$prefix ?? 0 }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td>Damaged</td>
                                @foreach($data->taqmandata as $key => $taqmandata)
                                    @php
                                        $prefix = $taqmandata['alias'].$damagedsuffix;
                                    @endphp
                                    <td>{{ $kits->$prefix ?? 0 }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td>To be Used</td>
                                @foreach($data->taqmandata as $key => $taqmandata)
                                    @php
                                        $receivedprefix = $taqmandata['alias'].$receivedsuffix;
                                        $damagedprefix = $taqmandata['alias'].$damagedsuffix;
                                    @endphp
                                    <td>{{ ($kits->$receivedprefix-$kits->$damagedprefix) ?? 0 }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                    @empty

                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts') 

    @component('/tables/scripts')

    @endcomponent
@endsection
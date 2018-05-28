@extends('layouts.tasks')

@section('css_scripts')
    
@endsection

@section('custom_css')
	<style type="text/css">
		.input-edit {
            background-color: #FFFFCC;
        }
	</style>
@endsection

@section('content')
@php
    $prevmonth = date('m')-1;
@endphp
<div class="row">
    <div class="col-md-12">
        <div class="hpanel" style="margin-top: 1em;margin-right: 18%;">
            <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                {{ Form::open(['url' => '/kitsdeliveries', 'method' => 'post', 'class'=>'form-horizontal']) }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @component('/forms/scripts')
        @slot('js_scripts')
            
        @endslot


        @slot('val_rules')
           
        @endslot

    @endcomponent
@endsection
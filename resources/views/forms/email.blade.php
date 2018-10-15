@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/summernote/summernote.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

    <div class="small-header">
        <div class="hpanel">
            <div class="panel-body">
                <h2 class="font-light m-b-xs">
                    Emails
                </h2>
            </div>
        </div>
    </div>

   <div class="content">
        <div>

        @if (isset($email))
            {{ Form::open(['url' => '/email/' . $email->id, 'method' => 'put', 'class'=>'form-horizontal']) }}
        @else
            {{ Form::open(['url'=>'/email', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'samples_form']) }}
            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
        @endif

        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-heading">
                        <center> </center>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Name
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <input class="form-control" required type="text" name="name" value="{{ $email->name ?? '' }}">
                            </div>
                        </div> 

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Subject
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <input class="form-control" required type="text" name="subject" value="{{ $email->subject ?? '' }}">
                            </div>
                        </div> 

                        <div class="form-group">
                            <label class="col-sm-4 control-label">From Name</label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" name="from_name" value="{{ $email->from_name ?? '' }}">
                            </div>
                        </div> 

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Use Lab Signature</label>
                            <div class="col-sm-8">
                                <input type="checkbox" class="i-checks" name="lab_signature" value=1
                                @if(isset($email) && $email->lab_signature)
                                    checked
                                @endif
                                > Tick if yes
                            </div>
                        </div> 

                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">CC List (Coma Separated)</label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" name="cc_list" value="{{ $email->cc_list ?? '' }}">
                            </div>
                        </div> 

                        <div class="form-group">
                            <label class="col-sm-4 control-label">BCC List (Coma Separated)</label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" name="bcc_list" value="{{ $email->bcc_list ?? '' }}">
                            </div>
                        </div> 

                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Email Content</label>
                            <div class="col-sm-8">
                                <textarea name="email_content" value="{{ $email->content ?? '' }}" id="email_content">
                                    
                                </textarea>
                            </div>
                        </div> 


                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-4">

                                @if (isset($email))
                                    <button class="btn btn-success" type="submit">Update Email</button>
                                @else
                                    <button class="btn btn-success" type="submit">Save Email</button>
                                @endif     
                                
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

        {{ Form::close() }}

      </div>
    </div>

@endsection

@section('scripts')

    @component('/forms/scripts')
        @slot('js_scripts')
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
            <script src="{{ asset('js/summernote/summernote.js') }}"></script>
        @endslot

        $('#email_content').summernote();
    @endcomponent

@endsection

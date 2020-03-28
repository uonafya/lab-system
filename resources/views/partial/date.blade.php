                        <?php $prop2 = $prop2 ?? null; ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">  {{ $label }}
                                @if(isset($required) && $required)
                                    <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                @endif
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group date {{ $class ?? 'date-normal' }}">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" value="{{ $model->$prop2->$prop ?? $model->$prop ?? '' }}" id="{{ $prop }}" name="{{ $prop }}" 
                                    @if(isset($required) && $required)
                                        required  class="form-control requirable"
                                    @else
                                        class="form-control"
                                    @endif
                                    >
                                </div>
                            </div>                            
                        </div>
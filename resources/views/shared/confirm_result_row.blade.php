

                                    @php

                                        if(in_array(env('APP_LAB'), $double_approval) && $worksheet->status_id == 2){

                                            if($sample->has_rerun){
                                                $class = 'noneditable';
                                            }
                                            else{
                                                $class = 'editable';
                                            }
                                        }

                                        /*if(in_array(env('APP_LAB'), $double_approval) && $editable){
                                            
                                            if($sample->repeatt == 1){
                                                $class = 'noneditable';
                                            }
                                            else{
                                                $class = 'editable';
                                            }
                                        }*/
                                    @endphp

                                    <tr>
                                        <td> 
                                            {{ $sample->patient->patient }} 

                                            <input type="hidden" name="samples[]" value="{{ $sample->id }}" class="{{ $class }}">
                                            <input type="hidden" name="batches[]" value="{{ $sample->batch_id }}" class="{{ $class }}">
                                        </td>
                                        <td> {{ $sample->id }}  </td>
                                        <td> {{ $sample->run }} </td>
                                        <td> {{ $sample->interpretation }} </td>
                                        <td>  
                                            @if($sample->approvedby)
                                                @foreach($results as $result)
                                                    @if($sample->result == $result->id)
                                                        {!! $result->name_colour !!}
                                                    @endif
                                                @endforeach

                                            @else
                                                <select name="results[]" class="{{ $class }}">
                                                    @foreach($results as $result)
                                                        <option value="{{$result->id}}"
                                                            @if(($sample->result == $result->id) || (!$sample->result && $result->id == 5))
                                                                selected
                                                            @endif
                                                            > {!! $result->name !!} </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </td>

                                        <td> 
                                            @if($sample->approvedby)
                                                @foreach($actions as $action)
                                                    @if($sample->repeatt == $action->id)
                                                        {!! $action->name_colour !!}
                                                    @endif
                                                @endforeach

                                            @else
                                                <select name="actions[]" class="{{ $class }}">
                                                    <option>Choose an action</option>
                                                    @foreach($actions as $action)
                                                        <option value="{{$action->id}}"
                                                            @if($sample->repeatt == $action->id)
                                                                selected
                                                            @endif
                                                            > {{ $action->name }} </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </td>

                                        <td> {{ $sample->dateapproved }} </td>
                                        <td> {{ $sample->approver->full_name ?? '' }} </td>
                                        <td> 
                                            <a href="{{ url('sample/' . $sample->id) }}" title='Click to view Details' target='_blank'> Details</a> | 
                                            <a href="{{ url('sample/runs/' . $sample->id) }}" title='Click to View Runs' target='_blank'>Runs </a>  
                                        </td>
                                    </tr>
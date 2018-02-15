<!-- Jquery Validate -->
<script src="{{ asset('js/validate/jquery.validate.min.js') }}"></script>
<script src="{{ asset('js/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('js/select2/select2.full.min.js') }}"></script>

{{ $js_scripts or '' }}

<script type="text/javascript">
    $(document).ready(function(){
    	$(".form-control").attr('autocomplete', 'off');

        $("select").select2();
        
        /*$('.data-table').DataTable({
            pageLength: 10,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [
                { 
                    extend: 'copy',
                    title: 'Download',
                    exportOptions:{
                        columns: ':not(.not-export-column)'
                    }
                },
                {
                    extend: 'csv', 
                    title: 'Download',
                    exportOptions:{
                        columns: ':not(.not-export-column)'
                    }
                },
                {
                    extend: 'excel', 
                    title: 'Download',
                    exportOptions:{
                        columns: ':not(.not-export-column)'
                    }
                },
                {
                    extend: 'pdf', 
                    title: 'Download',
                    exportOptions:{
                        columns: ':not(.not-export-column)'
                    }
                },

                {
                    extend: 'print',
                    customize: function (win){
                        $(win.document.body).addClass('white-bg');
                        $(win.document.body).css('font-size', '10px');

                        $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                    },
                    exportOptions:{
                        columns: ':visible(:not(.not-export-column))'
                    }
                }
            ]

        });*/

        // $('.data-table').DataTable({
        //     pageLength: 10,
        //     responsive: true

        // });

        var msg;
        var dynamicErrorMsg = function () { return msg; }


        

        $(".form-horizontal").validate({
            errorPlacement: function (error, element)
            {
                element.before(error);
            }
            {{ $val_rules or '' }}
        });

        {{ $slot }}

    });

</script>

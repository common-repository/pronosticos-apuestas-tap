(function ( $ ) {
	"use strict";

	$(function () {
        var dataTableConfig = {
            "ajax": {
                "url": suscripciones.dataTable.ajax.url,
                "data": function( d ){
                    var filter_data = {
                        "filter[execute]" : $( "#filtro_execute" ).val(),
                        "filter[fecha_inicio]" : $( "#filtro_fecha_inicio" ).val(),
                        "filter[fecha_fin]" : $( "#filtro_fecha_fin" ).val(),
                    };
                    var data = $.extend({}, d, filter_data);
                    return data;
                }
            },
            "initComplete": function(){
                var $api = this.api();

                $("#filtro_button").on("click", function(){
                    $("#filtro_execute").val(1);
                    var search_text = ""+$("input[type=search]").val();
                    $api.search(search_text).draw();
                });
            }
        };
        var dtConfig = $.extend({}, suscripciones.dataTable, dataTableConfig);
        $( "#lista_suscripciones" ).dataTable(dtConfig);

        $( "#filtro_fecha_inicio" ).datepicker({
            changeMonth: true,
            minDate: null,
            maxDate: null,
            onClose: function( selectedDate ) {
                $( "#filtro_fecha_fin" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#filtro_fecha_fin" ).datepicker({
            changeMonth: true,
            minDate: null,
            maxDate: null,
            onClose: function( selectedDate ) {
                $( "#filtro_fecha_inicio" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
	});

}(jQuery));
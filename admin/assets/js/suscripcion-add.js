(function ( $ ) {
	"use strict";

	$(function () {

        $( "#suscripcion_numero" ).select2({
            allowClear: true,
            placeholder: "Seleccione una opcion"
        });

        $( "#suscripcion_fecha_fin" ).datepicker({
            changeMonth: true,
            minDate: null,
            maxDate: null
        });

	});

}(jQuery));
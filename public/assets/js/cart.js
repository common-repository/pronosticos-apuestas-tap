(function ( $ ) {
	"use strict";

	$(function () {

		function validar_cupon(){
            var cart = $('#cart').val();
            var cupon = $('#cupon').val();
            $.ajax({
                url: cartVars.url,
                type: 'POST',
                dataType: 'json',
                data:'action=pronostico_apuestas_validar_cupon&cart='+cart+'&cupon='+cupon,
                beforeSend: function(){
                    $('button.validar-cupon').addClass('hidden');
                    $('button.cupon-spinner').removeClass('hidden');
                    $('#validar-cupon').css('width', 143);
                },
                success: function(data, status, xhr){
                    if(status === 'success'){
                        $('#descuento').html(data.descuento);
                        $('#total').html(data.total);
                        if(data.valido){
                            $('span.cupon-valid').removeClass('hidden').fadeIn(300).delay(5000).fadeOut(3000);
                            $('span.cupon-invalid').addClass('hidden');
                        }else{
                            $('span.cupon-invalid').removeClass('hidden').fadeIn(300).delay(5000).fadeOut(3000);
                            $('span.cupon-valid').addClass('hidden');
                        }
                    }
                    $('button.cupon-spinner').addClass('hidden');
                    $('button.validar-cupon').removeClass('hidden');
                    $('#validar-cupon').css('width', 164);
                }
            });
        }

        $('button.validar-cupon').on('click', validar_cupon);
        $('input#cupon').on('focusout', validar_cupon);

        $('select.suscripcion-periodo').change(function(){
            var parent = $(this).parent();
            var spinner = parent.parent().parent().find('span.suscripcion-periodo-spinner');
            var params = parent.serialize();
            var cart = $('#cart').val();
            var cupon = $('#cupon').val();
            $.ajax({
                url: cartVars.url,
                type: 'POST',
                dataType: 'json',
                data:'action=pronostico_apuestas_update_shopping_cart&cart='+cart+'&'+params+'&cupon='+cupon,
                beforeSend: function(){
                    spinner.removeClass('hidden');
                },
                success: function(data, status, xhr){
                    if(status === 'success'){
                        $('.input-tipster-'+data.tipster+'-suscripcion-precio').val(data.precio);
                        $('.text-tipster-'+data.tipster+'-suscripcion-precio').html(data.precio);
                        $('#subtotal').html(data.subtotal);
                        $('#descuento').html(data.descuento);
                        $('#total').html(data.total);
                    }
                    spinner.addClass('hidden');
                }
            });
        });

        $('#metodo-pago-paypal input[type="image"]').on('click',function(e){
            e.preventDefault();
            var $form = $('#metodo-pago-paypal');
            var input_return = $form.find('input[name="return"]');
            var input_cancel_return = $form.find('input[name="cancel_return"]');
            var cart = $('#cart').val();
            $.ajax({
                url: cartVars.url,
                type: 'POST',
                dataType: 'json',
                data:'action=pronostico_apuestas_confirm_paypal&cart='+cart,
                success: function(data, status, xhr){
                    if(status === 'success' && data.success){
                        var url_return = input_return.val();
                        var url_cancel_return = input_cancel_return.val();
                        input_return.val(url_return + '?token='+data.token+'&pid='+data.pid);
                        input_cancel_return.val(url_cancel_return + '?token='+data.token+'&pid='+data.pid);
                        $form.submit();
                    }
                }
            });
        });

	});

}(jQuery));
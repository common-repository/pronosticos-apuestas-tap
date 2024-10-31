<div class="col-xs-12">
    <h2>Gracias por suscribirte al servicio <strong>premium</strong> de <a href="<?php echo esc_url( home_url('/') ); ?>">Apuesta Blog</a>.</h2>
    <p>Has elegido como m&eacute;todo de pago <strong>Paysafecard</strong>, para confirmar tu suscripci&oacute;n debes enviarnos la cantidad de <span id="total" class="text-bold" style="font-size: 110%"><?php echo number_format($cart->getTotal(), 2, '.', ''); ?></span> <i class="fa fa-euro"></i> en pines de <strong>Paysafecard</strong> a <a href="mailto:contacto@apuestablog.com">contacto@apuestablog.com</a>.</p>
    <p>Tu n&uacute;mero de orden es: <strong><?php echo $cart->getNumero() ?></strong>.</p>
    <p>Una vez confirmemos que los pines tienen saldo y son v&aacute;lidos activaremos la cuenta. Este proceso es manual as&iacute; que por favor ten paciencia.</p>
    <p>Cuando el tipster publique los pron&oacute;sticos recibir&aacute;s autom&aacute;ticamente un correo electr&oacute;nico </p>
</div>
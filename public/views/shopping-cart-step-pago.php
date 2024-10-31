<?php global $current_user; ?>
<div class="col-xs-12">
    <ul class="nav nav-tabs nav-justified marL0">
        <li role="presentation" class="disabled">
            <a>
                <i class="fa fa-shopping-cart"></i> Resumen
            </a>
        </li>
        <li role="presentation" class="active">
            <a href="#">
            <?php if(empty($current_user->user_email)): ?>
                <i class="fa fa-user"></i> Contacto
            <?php else: ?>
                <i class="fa fa-credit-card"></i> Pago
            <?php endif;?>
            </a>
        </li>
        <li role="presentation"><a class="inactive">&nbsp;</a></li>
    </ul>
    <?php if(empty($current_user->user_email)): ?>
    <div class="alert alert-warning marT50" role="alert">
        <p>Estimado(a) <strong><?php echo $current_user->display_name; ?></strong>, hemos detectado que no tienes establecida la direcci&oacute;n de email en tu perfil.</p>
        <p>Por favor visite su <a href="<?php echo esc_url( admin_url( 'profile.php' ) );?>" class="alert-link">perfil</a> y establezca su direcci&oacute;n de email para que podamos ofrecerle un servicio personalizado.</p>
        <p>Muchas gracias.</p>
        <p class="marT20">
            <a class="btn btn-default" href="<?php echo esc_url( get_post_permalink(get_option('PRONOSTICO_APUESTAS_PAGINA_CARRITO')) ); ?>"><i class="fa fa-chevron-left"></i> Regresar </a>
            <a class="btn btn-warning" href="<?php echo esc_url( admin_url( 'profile.php' ) );?>">Ir al perfil <i class="fa fa-chevron-right"></i></a>
        </p>
    </div>
    <?php else: ?>
    <table class="table table-responsive">
        <thead>
            <tr>
                <th class="text-center" style="width: 45%;">Orden</th>
                <th style="width: 55%;">Metodo de Pago</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <ul class="list-unstyled marL0 marB0">
                        <?php $paypal_item_name = 'Orden #'.$cart->getNumero().': '; ?>
                        <?php $pedido_suscripcion = $cart->getElementos(); ?>
                        <?php foreach ( $pedido_suscripcion as $elemento ) :
                        $pedido_tipster_id = $elemento['tipster'];
                        $pedido_periodo = $elemento['periodo'];
                        $pedido_precio = $elemento['precio'];?>
                        <li class="text-uppercase text-right">
                            <span>
                                <?php echo sprintf('<a href="%s">%s</a>', get_the_permalink($pedido_tipster_id), get_the_title($pedido_tipster_id)) ?> x
                                <?php echo sprintf(_n('%s MES', '%s MESES', $pedido_periodo, 'epic'), $pedido_periodo); ?>
                                <?php $paypal_item_name .= sprintf(_n('%s MES', '%s MESES', $pedido_periodo, 'epic'), $pedido_periodo);?>
                                <?php $paypal_item_name .= ' suscripcion a pronosticos de '.get_the_title($pedido_tipster_id).'. ';?>
                            </span> :
                            <span>
                                <span class="text-bold text-tipster-<?php echo $pedido_tipster_id ?>-suscripcion-precio" style="font-size: 120%"><?php echo number_format($pedido_precio, 2, '.', '');?></span> <i class="fa fa-euro"></i>
                            </span>
                        </li><?php
                        endforeach; ?>
                        <li class="text-uppercase text-right">
                            <span class="text-bold">Subtotal</span> :
                            <span id="subtotal"><?php echo number_format($cart->getSubtotal(), 2, '.', ''); ?></span> <i class="fa fa-euro"></i>
                        </li>
                        <li class="text-uppercase text-right">
                            <span class="text-bold">Descuento</span> :
                            <span id="descuento"><?php echo number_format($cart->getDescuento(), 2, '.', ''); ?></span> <i class="fa fa-euro"></i>
                        </li>
                        <li class="text-uppercase text-right">
                            <span class="text-bold">Total</span> :
                            <span id="total" class="text-bold" style="font-size: 120%"><?php echo number_format($cart->getTotal(), 2, '.', ''); ?></span> <i class="fa fa-euro"></i>
                        </li>
                    </ul>
                </td>
                <td>
                    <p class="marB0">Haga clic en una de las opciones siguientes:</p>
                    <?php $metodos_pago = get_option('PRONOSTICO_APUESTAS_METODOS_PAGO'); ?>
                    <ul class="list-unstyled marL0">
                        <?php if(intval($metodos_pago['paysafecard']['activado'])): ?>
                        <li class="padT5 padB5">
                            <form method="post" action="<?php echo esc_url( get_post_permalink(get_option('PRONOSTICO_APUESTAS_PAGINA_CARRITO')) ); ?>">
                                <input type="hidden" name="step" value="paysafecard">
                                <input type="hidden" name="pedido" value="<?php echo $cart->getHash(); ?>">
                                <input type="image" src="<?php echo epic_template_directory_uri()?>/img/paysafecard-logo.png"/>
                            </form>
                        </li>
                        <?php endif; ?>
                        <?php if(intval($metodos_pago['paypal']['activado'])): ?>
                        <li class="padT5 padB5"><?php
                            $mode = $metodos_pago['paypal']['mode'];
                            $path = $metodos_pago['paypal'][$mode]['path'];
                            $account = $metodos_pago['paypal'][$mode]['account'];?>
                            <form id="metodo-pago-paypal" method="post" action="https://www.<?php echo $path ?>.com/cgi-bin/webscr" target="<?php echo $metodos_pago['paypal']['target']; ?>">
                                <input type="hidden" name="cmd" value="_xclick">
                                <input type="hidden" name="business" value="<?php echo $account; ?>" />
                                <input type="hidden" name="item_name" value="<?php echo $paypal_item_name; ?>" />
                                <input type="hidden" name="currency_code" value="<?php echo $metodos_pago['paypal']['currency']; ?>">
                                <input type="hidden" name="amount" value="<?php echo $cart->getTotal(); ?>">
                                <input type="hidden" name="lc" value="<?php echo $metodos_pago['paypal']['language']; ?>">
                                <input type="hidden" name="paymentaction" value="<?php echo $metodos_pago['paypal']['payment_action']; ?>">
                                <input type="hidden" name="return" value="<?php echo $metodos_pago['paypal']['url']['return']; ?>">
                                <input type="hidden" name="cancel_return" value="<?php echo $metodos_pago['paypal']['url']['cancel']; ?>">
                                <input type="hidden" id="cart" value="<?php echo $cart->getHash(); ?>">
                                <input type="image" src="https://www.paypalobjects.com/es_ES/i/btn/x-click-but01.gif" border="0" name="submit" alt="PayPal. La forma r&aacute;pida y segura de pagar en Internet.">
                                <img alt="" border="0" src="https://www.paypalobjects.com/es_ES/i/scr/pixel.gif" width="1" height="1" style='border:none;display:none;'>
                            </form>
                        </li>
                        <?php endif; ?>
                        <?php if(intval($metodos_pago['skrill']['activado'])): ?>
                        <li>Skrill</li>
                        <?php endif; ?>
                    </ul>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">
                    <a class="btn btn-default" href="<?php echo esc_url( get_post_permalink(get_option('PRONOSTICO_APUESTAS_PAGINA_CARRITO')) ); ?>"><i class="fa fa-chevron-left"></i> Regresar </a>
                </td>
            </tr>
        </tfoot>
    </table>
    <?php endif;?>
</div>
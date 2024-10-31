<div class="col-xs-12">
    <ul class="nav nav-tabs nav-justified marL0">
        <li role="presentation" class="active">
            <a href="#">
                <i class="fa fa-shopping-cart"></i> Resumen
            </a>
        </li>
        <li role="presentation" class="disabled">
            <a>
                <i class="fa fa-credit-card"></i> Pago
            </a>
        </li>
        <li role="presentation"><a class="inactive">&nbsp;</a></li>
    </ul>
    <table class="table table-responsive">
        <thead>
        <tr>
            <th>Tipster</th>
            <th>Periodo</th>
            <th>&nbsp;</th>
            <th>Precio</th>
        </tr>
        </thead>
        <tbody>
        <?php $pedido_suscripcion = $cart->getElementos(); ?>
        <?php foreach ( $pedido_suscripcion as $elemento ) :
            $pedido_tipster_id = $elemento['tipster'];
            $pedido_periodo = $elemento['periodo'];
            $pedido_precio = $elemento['precio']; ?>
            <tr>
                <td><?php echo sprintf('<a href="%s">%s</a>', get_the_permalink($pedido_tipster_id), get_the_title($pedido_tipster_id)) ?></td>
                <td>
                    <form role="form" name="tipster[<?php echo $pedido_tipster_id ?>]">
                        <input type="hidden" name="tipster" value="<?php echo $pedido_tipster_id ?>">
                        <select name="periodo" class="form-control suscripcion-periodo tipster-<?php echo $pedido_tipster_id ?>-suscripcion-periodo"><?php
                        $tipster_suscripcion = get_post_meta($pedido_tipster_id, '_tipster_suscripcion', true);
                        foreach ( $tipster_suscripcion as $suscripcion ):
                            $periodo = $suscripcion['periodo'];?>
                            <option value="<?php echo $periodo ?>" <?php if($periodo === $pedido_periodo):?>selected="selected"<?php endif;?>>
                                <?php echo sprintf(_n('%s MES', '%s MESES', $periodo, 'epic'), $periodo); ?>
                            </option><?php
                        endforeach;?>
                        </select>
                    </form>
                </td>
                <td>
                    <span class="fa fa-spinner fa-pulse fa-2x fa-fw hidden suscripcion-periodo-spinner"></span>
                </td>
                <td>
                    <input name="precio" class="input-tipster-<?php echo $pedido_tipster_id ?>-suscripcion-precio" type="hidden" value="<?php echo $pedido_precio; ?>">
                    <span class="text-bold text-tipster-<?php echo $pedido_tipster_id ?>-suscripcion-precio" style="font-size: 120%"><?php echo number_format($pedido_precio, 2, '.', '');?></span> <i class="fa fa-euro"></i>
                </td>
            </tr>
        <?php
        endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td class="text-uppercase text-right">Subtotal</td>
                <td><span id="subtotal"><?php echo number_format($cart->getSubtotal(), 2, '.', ''); ?></span> <i class="fa fa-euro"></i></td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td class="text-uppercase text-right">Promocion</td>
                <td>
                    <div id="validar-cupon" class="input-group" style="width: 164px;">
                        <input id="cupon" name="cupon" class="form-control input-cupon" style="width: 100px">
                        <span class="fa fa-check hidden cupon-valid"></span>
                        <span class="fa fa-times hidden cupon-invalid"></span>
                        <span class="input-group-btn">
                            <button class="btn btn-default validar-cupon" type="button">Validar</button>
                            <button class="btn btn-default hidden cupon-spinner" type="button">
                                <span class="fa fa-spinner fa-pulse fa-fw"></span>
                            </button>
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td class="text-uppercase text-right">Descuento</td>
                <td><span id="descuento"><?php echo number_format($cart->getDescuento(), 2, '.', ''); ?></span> <i class="fa fa-euro"></i></td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;<input type="hidden" id="cart" name="cart" value="<?php echo $cart->getHash(); ?>"></td>
                <td class="text-uppercase text-right">Total</td>
                <td><span id="total" class="text-bold" style="font-size: 120%"><?php echo number_format($cart->getTotal(), 2, '.', ''); ?></span> <i class="fa fa-euro"></i></td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;</td>
                <td>
                    <?php if(is_user_logged_in()):?>
                    <form method="post" action="<?php echo esc_url( get_post_permalink(get_option('PRONOSTICO_APUESTAS_PAGINA_CARRITO')) ); ?>">
                        <input type="hidden" name="step" value="pago">
                        <input type="hidden" name="pedido" value="<?php echo $cart->getHash(); ?>">
                        <button type="submit" class="btn btn-success">Continuar <i class="fa fa-chevron-right"></i> </button>
                    </form>
                    <?php else:?>
                    <a class="btn btn-info" href="<?php echo esc_url( wp_login_url( get_post_permalink(get_option('PRONOSTICO_APUESTAS_PAGINA_CARRITO')) ) ); ?>">Iniciar sesi&oacute;n</a>
                    <?php endif;?>
                </td>
            </tr>
        </tfoot>
    </table>

</div>
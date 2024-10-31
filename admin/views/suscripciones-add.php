<?php
use PronosticosApuestasTAP\Common\Suscripcion;
use PronosticosApuestasTAP\Frontend\Pronosticos_Apuestas_TAP;
?>

<?php settings_errors('pronostico-apuestas-suscripcion'); ?>

<div class="card">
    <h3><?php _e('Agregar suscripcion', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></h3>
    <hr/>
    <form id="form-pronostico-apuestas-suscripcion" role="form" method="post" action="<?php echo admin_url( 'admin.php?page='.Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()."/suscripciones&settings-updated=1" ) ?>">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="suscripcion_numero"><?php _e('Numero', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                    </th>
                    <td>
                        <select id="suscripcion_numero" name="suscripcion[numero]" required="required">
                            <option></option>
                        <?php foreach( $pedidos as $pedido ): ?>
                            <option value="<?php echo $pedido->getNumero(); ?>"><?php echo $pedido->getNumero(); ?></option>
                        <?php endforeach?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="suscripcion_fecha_fin"><?php _e('Fecha fin', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                    </th>
                    <td>
                        <input id="suscripcion_fecha_fin" type="text" name="suscripcion[fecha_fin]">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="suscripcion_forma_pago"><?php _e('Forma de Pago', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                    </th>
                    <td>
                        <?php $metodos_pago = get_option('PRONOSTICO_APUESTAS_METODOS_PAGO'); ?>
                        <select id="suscripcion_forma_pago" name="suscripcion[forma_pago]" required="required">
                            <option value="">Seleccione</option>
                            <?php if(intval($metodos_pago['paysafecard']['activado'])): ?>
                            <option value="<?php echo Suscripcion::PAYSAFECARD; ?>" ><?php echo Suscripcion::PAYSAFECARD; ?></option>
                            <?php endif; ?>
                            <?php if(intval($metodos_pago['paypal']['activado'])): ?>
                            <option value="<?php echo Suscripcion::PAYPAL; ?>" ><?php echo Suscripcion::PAYPAL; ?></option>
                            <?php endif; ?>
                            <?php if(intval($metodos_pago['skrill']['activado'])): ?>
                            <option value="<?php echo Suscripcion::SKRILL; ?>" ><?php echo Suscripcion::SKRILL; ?></option>
                            <?php endif; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="suscripcion_estado"><?php _e('Estado', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                    </th>
                    <td>
                        <select id="suscripcion_estado" name="suscripcion[estado]">
                            <option value="<?php echo Suscripcion::PENDIENTE; ?>" ><?php echo Suscripcion::PENDIENTE; ?></option>
                            <option value="<?php echo Suscripcion::INACTIVO; ?>" ><?php echo Suscripcion::INACTIVO; ?></option>
                            <option value="<?php echo Suscripcion::ACTIVO; ?>" ><?php echo Suscripcion::ACTIVO; ?></option>
                        </select>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <input type="hidden" name="op" value="add">
                        <?php wp_nonce_field( 'form_pronostico_apuestas_suscripcion_add', 'suscripcion[_crsf_token]' ); ?>
                        <button type="submit" class="button button-primary">
                            <?php _e('Guardar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?>
                        </button>
                        <a class="button button-secondary" href="<?php echo admin_url( 'admin.php?page='.Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()."/suscripciones" ) ?>">
                            <?php _e('Cancelar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?>
                        </a>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
</div>
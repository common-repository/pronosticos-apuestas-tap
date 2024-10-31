<?php
use PronosticosApuestasTAP\Common\Suscripcion;
use PronosticosApuestasTAP\Common\TipsterRepository;
use PronosticosApuestasTAP\Frontend\Pronosticos_Apuestas_TAP;
?>

<?php settings_errors('pronostico-apuestas-suscripcion'); ?>

<div class="card">
    <h3><?php _e('Editar suscripcion', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></h3>
    <hr>
    <form action="<?php echo admin_url( 'admin.php?page='.Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()."/suscripciones&settings-updated=1" ) ?>" method="post">
        <input type="hidden" name="op" value="delete">
        <input type="hidden" name="suscripcion[id]" value="<?php echo $suscripcion->getId(); ?>">
        <?php wp_nonce_field( 'form_pronostico_apuestas_suscripcion_delete_'.$suscripcion->getId(), 'suscripcion[_crsf_token]' ); ?>
        <button type="submit" class="button action">
            <?php _e('Eliminar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?>
        </button>
    </form>
    <form id="form-pronostico-apuestas-suscripcion" role="form" method="post" action="<?php echo admin_url( 'admin.php?page='.Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()."/suscripciones&settings-updated=1" ) ?>">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label><?php _e('Numero', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                    </th>
                    <td>
                        <label><?php echo $suscripcion->getNumero();?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php _e('Usuario', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                    </th>
                    <td>
                        <label><?php echo $suscripcion->getUsuario()->display_name;?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php _e('Tipster', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                    </th>
                    <td>
                        <label>
                            <?php
                            $tipster = TipsterRepository::get_instance()->findBy($suscripcion->getTipster());
                            if(null !== $tipster){
                                echo get_the_title($tipster->ID);
                            }
                            ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php _e('Periodo', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                    </th>
                    <td>
                        <label><?php echo sprintf(_n('%s MES', '%s MESES', $suscripcion->getPeriodo(), 'epic'), $suscripcion->getPeriodo()); ?></label>
                    </td>
                </tr>
                <?php if(strcmp($suscripcion->getEstado(), Suscripcion::ACTIVO) === 0):?>
                <tr>
                    <th scope="row">
                        <label><?php _e('Fecha Fin', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                    </th>
                    <td>
                        <label><?php echo date('d/m/Y H:i', $suscripcion->getFechaFin()); ?></label>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th scope="row">
                        <label><?php _e('Forma de Pago', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                    </th>
                    <td>
                        <label><?php echo $suscripcion->getFormaDePago(); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="promocion_estado"><?php _e('Estado', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                    </th>
                    <td>
                        <select id="promocion_estado" name="suscripcion[estado]">
                            <option value="<?php echo Suscripcion::PENDIENTE; ?>" <?php if(strcmp($suscripcion->getEstado(), Suscripcion::PENDIENTE) === 0):?>selected="selected"<?php endif?> ><?php echo Suscripcion::PENDIENTE; ?></option>
                            <option value="<?php echo Suscripcion::INACTIVO; ?>" <?php if(strcmp($suscripcion->getEstado(), Suscripcion::INACTIVO) === 0):?>selected="selected"<?php endif?> ><?php echo Suscripcion::INACTIVO; ?></option>
                            <option value="<?php echo Suscripcion::ACTIVO; ?>" <?php if(strcmp($suscripcion->getEstado(), Suscripcion::ACTIVO) === 0):?>selected="selected"<?php endif?> ><?php echo Suscripcion::ACTIVO; ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="suscripcion_fecha_fin"><?php _e('Fecha fin', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                    </th>
                    <td>
                        <input id="suscripcion_fecha_fin" type="text" name="suscripcion[fecha_fin]" value="<?php echo date('d/m/Y', $suscripcion->getFechaFin()); ?>">
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <input type="hidden" name="op" value="update">
                        <input type="hidden" name="suscripcion[id]" value="<?php echo $suscripcion->getId(); ?>">
                        <?php wp_nonce_field( 'form_pronostico_apuestas_suscripcion_update', 'suscripcion[_crsf_token]' ); ?>
                        <button type="submit" class="button button-primary">
                            <?php _e('<strong>Editar</strong>', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?>
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
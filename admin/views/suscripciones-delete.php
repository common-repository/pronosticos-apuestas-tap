<?php
use PronosticosApuestasTAP\Frontend\Pronosticos_Apuestas_TAP;
?>

<?php settings_errors('pronostico-apuestas-suscripcion'); ?>

<div class="card">
    <h3><?php _e('Eliminar suscripcion', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></h3>
    <hr/>
    <a class="button button-secondary" href="<?php echo admin_url( 'admin.php?page='.Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()."/suscripciones" ) ?>">
        <?php _e('Regresar al listado', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?>
    </a>
</div>
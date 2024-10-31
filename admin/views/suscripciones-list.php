<?php
use PronosticosApuestasTAP\Common\Suscripcion;
use PronosticosApuestasTAP\Common\TipsterRepository;
use PronosticosApuestasTAP\Frontend\Pronosticos_Apuestas_TAP;
?>
<h3><?php _e('Lista de suscripciones', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></h3>
<hr>
<table class="wp-list-table widefat">
    <thead>
        <tr>
            <th colspan="3">Filtros</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td scope="row">
                <label for="filtro_fecha_inicio"><?php _e('Fecha Fin: desde', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                <input type="text" id="filtro_fecha_inicio" name="filtro[fecha_inicio]">
                <label for="filtro_fecha_fin"><?php _e('hasta', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?></label>
                <input type="text" id="filtro_fecha_fin" name="filtro[fecha_fin]">
                <input id="filtro_execute" type="hidden" value="0">
                <button type="button" id="filtro_button" class="button action">
                    <?php _e('Filtrar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?>
                </button>
            </td>
        </tr>
    </tbody>
</table>
<br>
<table id="lista_suscripciones" class="wp-list-table widefat">
    <thead>
        <tr>
            <th data-type="num" data-data="numero" data-name="numero">Numero</th>
            <th data-type="usuario" data-data="usuario" data-name="usuario" data-orderable="false">Usuario</th>
            <th data-type="string" data-data="tipster" data-name="tipster" data-orderable="false">Tipster</th>
            <th data-type="string" data-data="periodo" data-name="periodo">Periodo</th>
            <th data-type="string" data-data="fecha_inicio" data-name="fecha_inicio">Fecha Inicio</th>
            <th data-type="string" data-data="fecha_fin" data-name="fecha_fin">Fecha Fin</th>
            <th data-type="string" data-data="forma_de_pago" data-name="forma_de_pago">Forma de Pago</th>
            <th data-type="string" data-data="estado" data-name="estado">Estado</th>
            <th data-type="html" data-data="accion" data-name="accion">
                <form action="<?php echo admin_url( 'admin.php?page='.Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug().'/suscripciones' ) ?>" method="post">
                    <input type="hidden" name="op" value="new">
                    <button type="submit" class="button-primary">
                        <?php _e('Agregar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?>
                    </button>
                </form>
            </th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
<?php ?>
<br>
<hr>
<form action="<?php echo admin_url( 'admin.php?page='.Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug().'/suscripciones' ) ?>" method="post">
    <input type="hidden" name="op" value="fix">
    <button type="submit" class="button-primary">
        <?php _e('Aplicar correccion', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()) ?>
    </button>
</form>

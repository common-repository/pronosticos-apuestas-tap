<?php $op = isset($_POST['op']) ? $_POST['op'] : 'list'; ?>
<div class="wrap">
    <h2><?php echo esc_html( get_admin_page_title() ); ?> &raquo; Suscripciones</h2>
    <div class="widefat">
        <?php do_action('pronostico_apuestas_gestion_suscripciones', $op); ?>
    </div>
</div>
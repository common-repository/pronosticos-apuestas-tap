<?php

namespace PronosticosApuestasTAP\Common;

class SuscriptionManager
{
    /**
     * @var null|\PronosticosApuestasTAP\Common\SuscriptionManager
     */
    protected static $instance = null;

    /**
     * @return null|\PronosticosApuestasTAP\Common\SuscriptionManager
     */
    public static function get_instance()
    {
        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param \PronosticosApuestasTAP\Common\Pedido $pedido
     */
    public function create(Pedido $pedido)
    {
        $usuario = $pedido->getUsuario();
        $elementos = $pedido->getElementos();
        foreach ( $elementos as $elemento ) {
            $suscripcion = new Suscripcion();
            $suscripcion->setNumero($pedido->getNumero());
            $suscripcion->setFormaDePago($pedido->getFormaDePago());
            $suscripcion->setUsuario($usuario);
            $estado = Suscripcion::PENDIENTE;
            $activa = false;
            if(strcmp($pedido->getEstado(), Pedido::PAGADO) === 0){
                $estado = Suscripcion::ACTIVO;
                $activa = true;
            }
            $suscripcion->setEstado($estado);

            $periodo = intval($elemento['periodo']);
            $suscripcion->setPeriodo($periodo);

            $tipster = intval($elemento['tipster']);
            $suscripcion->setTipster($tipster);

            $intervalo = new \DateInterval('P'.$periodo.'M1D');
            $now = new \DateTime('now');
            $dateStr = $now->format('Y-m-d');
            $fecha_fin = new \DateTime($dateStr);
            $fecha_fin->add($intervalo);
            $suscripcion->setFechaFin($fecha_fin->getTimestamp());

            SuscripcionRepository::get_instance()->persist($suscripcion);

            $user_meta_value = array(
                'suscripcion' => $suscripcion->getNumero(),
                'activa' => $activa,
                'fecha_fin' => $fecha_fin->getTimestamp()
            );

            update_user_meta($usuario->ID, 'pronostico_apuestas_suscripcion_activa_tipster_'.$tipster, $user_meta_value);
        }
    }

    public function update($s)
    {
        $suscripcion = SuscriptionManager::get_instance()->get_by_id($s['id']);
        $fechaFin = $s['fecha_fin'];
        $estado = null;
        $activa = false;
        switch($s['estado']){
            case Suscripcion::ACTIVO:
                $estado = Suscripcion::ACTIVO;
                $activa = true;
                break;
            case Suscripcion::INACTIVO:
                $estado = Suscripcion::INACTIVO;
                break;
            default: // Suscripcion::PENDIENTE
                $estado = Suscripcion::PENDIENTE;
                break;
        }
        $suscripcion->setEstado($estado);

        $periodo = $suscripcion->getPeriodo();
        $intervalo = new \DateInterval('P'.$periodo.'M1D');
        $now = new \DateTime('now');
        $dateStr = $now->format('Y-m-d');
        $fecha_fin = new \DateTime($dateStr);
        $fecha_fin->add($intervalo);
        if(!empty($fechaFin)){
            $now = \DateTime::createFromFormat('d/m/Y', $fechaFin);
            $dateStr = $now->format('Y-m-d');
            $intervalo = new \DateInterval('P1D');
            $fecha_fin = new \DateTime($dateStr);
            $fecha_fin->add($intervalo);
        }
        $suscripcion->setFechaFin($fecha_fin->getTimestamp());

        $ahora = new \DateTime('now');
        $suscripcion->setUpdatedAt($ahora->getTimestamp());

        SuscripcionRepository::get_instance()->update($suscripcion);

        $usuario = $suscripcion->getUsuario();
        $tipster = $suscripcion->getTipster();
        $user_meta = get_user_meta($usuario->ID, 'pronostico_apuestas_suscripcion_activa_tipster_'.$tipster, true);
        if(strcmp($user_meta['suscripcion'], $suscripcion->getNumero()) === 0){ //TODO: descomentar esta linea en la proxima actualizacion
            $user_meta_value = array(
                'suscripcion' => $suscripcion->getNumero(),
                'activa' => $activa,
                'fecha_fin' => $fecha_fin->getTimestamp()
            );

            update_user_meta($usuario->ID, 'pronostico_apuestas_suscripcion_activa_tipster_'.$tipster, $user_meta_value);
        } //TODO: descomentar esta linea en la proxima actualizacion

        if(strcmp(Suscripcion::PAYSAFECARD, $suscripcion->getFormaDePago()) === 0){
            do_action('pronostico_apuestas_enviar_email_suscripcion_por_paysafecard', $suscripcion);
        }

        if(strcmp(Suscripcion::PAYPAL, $suscripcion->getFormaDePago()) === 0){
            do_action('pronostico_apuestas_enviar_email_suscripcion_por_paypal_editada', $suscripcion);
        }

        return $suscripcion;
    }

    /**
     * @param array $s
     *
     * @return array
     */
    public function delete($s)
    {
        $response = array('success' => false, 'message' => 'La suscripcion no ha sido borrada.');
        $suscripcion = SuscriptionManager::get_instance()->get_by_id($s['id']);
        $result = SuscripcionRepository::get_instance()->delete($suscripcion);
        if($result){
            $response['success'] = true;
            $response['message'] = '';

            $usuario = $suscripcion->getUsuario();
            $tipster = $suscripcion->getTipster();
            $user_meta = get_user_meta($usuario->ID, 'pronostico_apuestas_suscripcion_activa_tipster_'.$tipster, true);
            if(strcmp($user_meta['suscripcion'], $suscripcion->getNumero()) === 0){
                delete_user_meta($usuario->ID, 'pronostico_apuestas_suscripcion_activa_tipster_'.$tipster);
            }
        }

        return $response;
    }

    /**
     * @return array
     */
    public function get_list()
    {
        return SuscripcionRepository::get_instance()->findAll();
    }

    /**
     * @param $id
     *
     * @return \PronosticosApuestasTAP\Common\Suscripcion
     */
    public function get_by_id($id)
    {
        return SuscripcionRepository::get_instance()->findById($id);
    }

    /**
     * @param $ahora
     * @param $estado
     */
    public function cancelar_suscripcion($ahora, $estado)
    {
        $suscripciones = SuscripcionRepository::get_instance()->getByFechaFinAndEstado($ahora, $estado);
        foreach ( $suscripciones as $suscripcion ) {
            $suscripcion->setEstado(Suscripcion::INACTIVO);
            $suscripcion->setUpdatedAt($ahora);

            SuscripcionRepository::get_instance()->update($suscripcion);

            $usuario = $suscripcion->getUsuario();
            $tipster = $suscripcion->getTipster();
            $user_meta = get_user_meta($usuario->ID, 'pronostico_apuestas_suscripcion_activa_tipster_'.$tipster, true);
            if(strcmp($user_meta['suscripcion'], $suscripcion->getNumero()) === 0){
                $user_meta_value = array(
                    'suscripcion' => $suscripcion->getNumero(),
                    'activa' => false,
                    'fecha_fin' => $ahora
                );

                update_user_meta($usuario->ID, 'pronostico_apuestas_suscripcion_activa_tipster_'.$tipster, $user_meta_value);
            }
        }
    }

    /**
     * @param $tipster_id
     * @param $estado
     *
     * @return array
     */
    public function get_suscripciones($tipster_id, $estado)
    {
        $suscripciones = SuscripcionRepository::get_instance()->getByTipsterAndEstado($tipster_id, $estado);
        return $suscripciones;
    }

    /**
     * @param \PronosticosApuestasTAP\Common\Pedido $pedido
     * @param                                       $suscription
     */
    public function createFromPedidoAndSuscripcion(Pedido $pedido, $suscription)
    {
        $estado = $suscription['estado'];
        $formaDePago = $suscription['forma_pago'];
        $fechaFin = $suscription['fecha_fin'];
        $usuario = $pedido->getUsuario();
        $elementos = $pedido->getElementos();
        foreach ( $elementos as $elemento ) { //TODO: encontrar una solucion mejor para cuando en el pedido hay suscripciones para mas de un tipster
            $suscripcion = new Suscripcion();
            $suscripcion->setNumero($pedido->getNumero());
            $suscripcion->setFormaDePago($pedido->getFormaDePago());
            if(null === $pedido->getFormaDePago() || strcmp($formaDePago, $pedido->getFormaDePago()) != 0){
                $suscripcion->setFormaDePago($formaDePago);
            }
            $suscripcion->setUsuario($usuario);
            $suscripcion->setEstado($estado);
            $activa = false;
            if(strcmp($estado, Suscripcion::ACTIVO) === 0){
                $activa = true;
            }

            $periodo = intval($elemento['periodo']);
            $suscripcion->setPeriodo($periodo);

            $tipster = intval($elemento['tipster']);
            $suscripcion->setTipster($tipster);

            $intervalo = new \DateInterval('P'.$periodo.'M1D');
            $now = new \DateTime('now');
            $dateStr = $now->format('Y-m-d');
            $fecha_fin = new \DateTime($dateStr);
            $fecha_fin->add($intervalo);
            if(!empty($fechaFin)){
                $now = \DateTime::createFromFormat('d/m/Y', $fechaFin);
                $dateStr = $now->format('Y-m-d');
                $intervalo = new \DateInterval('P1D');
                $fecha_fin = new \DateTime($dateStr);
                $fecha_fin->add($intervalo);
            }
            $suscripcion->setFechaFin($fecha_fin->getTimestamp());

            SuscripcionRepository::get_instance()->persist($suscripcion);

            $user_meta = get_user_meta($usuario->ID, 'pronostico_apuestas_suscripcion_activa_tipster_'.$tipster, true);
            if(strcmp($user_meta['suscripcion'], $suscripcion->getNumero()) === 0){
                $user_meta_value = array(
                    'suscripcion' => $suscripcion->getNumero(),
                    'activa' => $activa,
                    'fecha_fin' => $fecha_fin->getTimestamp()
                );

                update_user_meta($usuario->ID, 'pronostico_apuestas_suscripcion_activa_tipster_'.$tipster, $user_meta_value);
            }

            if(strcmp(Suscripcion::PAYSAFECARD, $suscripcion->getFormaDePago()) === 0){
                do_action('pronostico_apuestas_enviar_email_suscripcion_por_paysafecard', $suscripcion);
            }
        }
    }

    public function get_list_elements($start, $limit, $order, $search, $filter)
    {
        return SuscripcionRepository::get_instance()->getList($start, $limit, $order, $search, $filter);
    }

    public function get_total_elements()
    {
        return SuscripcionRepository::get_instance()->getTotalList();
    }

    public function get_total_filtred($search, $filter)
    {
        return SuscripcionRepository::get_instance()->getTotalFiltered($search, $filter);
    }

    public function fixRecords()
    {
        $suscripciones = SuscripcionRepository::get_instance()->findAll();
        foreach ( $suscripciones as $suscripcion ) {
            $usuario = $suscripcion->getUsuario();
            $tipster = $suscripcion->getTipster();
            $estado = $suscripcion->getEstado();
            $fecha_fin = $suscripcion->getFechaFin();

            $activa = false;
            if(strcmp($estado, Suscripcion::ACTIVO) === 0){
                $activa = true;
            }

            $user_meta = get_user_meta($usuario->ID, 'pronostico_apuestas_suscripcion_activa_tipster_'.$tipster, true);
            if(!empty($user_meta) && isset($user_meta['activa']) && isset($user_meta['fecha_fin'])){
                $user_meta_value = array(
                    'suscripcion' => $suscripcion->getNumero(),
                    'activa' => $activa,
                    'fecha_fin' => $fecha_fin
                );

                update_user_meta($usuario->ID, 'pronostico_apuestas_suscripcion_activa_tipster_'.$tipster, $user_meta_value);
            }
        }
    }
}
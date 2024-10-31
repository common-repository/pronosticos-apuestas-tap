<?php

namespace PronosticosApuestasTAP\Common;

class ShoppingCartManager
{
    /**
     * @var null|\PronosticosApuestasTAP\Common\ShoppingCartManager
     */
    protected static $instance = null;

    private $cookie = 'TAPASCID';

    /**
     * @return null|\PronosticosApuestasTAP\Common\ShoppingCartManager
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
     * @param $user
     *
     * @return null|\PronosticosApuestasTAP\Common\Pedido
     */
    public function getCurrentCart(\WP_User $user = null, $tipster = null)
    {
        $session_id = session_id();
        if(empty($session_id)) @session_start();
        if(isset($_SESSION[$this->cookie])){
            $cart = $this->getCart($_SESSION[$this->cookie]);
            if(null !== $cart){
                return $cart;
            }
        }

        return $this->initializeCart($user, $tipster);
    }

    /**
     * @return null|\PronosticosApuestasTAP\Common\Pedido
     */
    public function getCart($cookie)
    {
        $cart = PedidoRepository::get_instance()->findByCookie($cookie);

        return $cart;
    }

    /**
     * @param \WP_User $user
     * @param \WP_Post $tipster
     *
     * @return \PronosticosApuestasTAP\Common\Pedido
     */
    private function initializeCart(\WP_User $user = null, \WP_Post $tipster = null)
    {
        $session_id = session_id();
        if(empty($session_id)) @session_start();
        $tipster_id = null;
        $suscripcion_periodo = null;
        $suscripcion_precio = 0;
        $tipster_suscripcion = null;

        if($tipster instanceof \WP_Post){
            $tipster_id = $tipster->ID;
            $tipster_suscripcion = get_post_meta($tipster_id, '_tipster_suscripcion', true);

            if(isset($tipster_suscripcion[0])){
                $suscripcion_precio = floatval($tipster_suscripcion[0]['precio']);
                $suscripcion_periodo = $tipster_suscripcion[0]['periodo'];
            }
        }

        $pedido = new Pedido();
        $pedido->setUsuario($user);
        $suscripciones = array(
            $tipster_id => array(
                'tipster' => $tipster_id,
                'periodo' => $suscripcion_periodo,
                'precio'  => $suscripcion_precio
            )
        );
        $pedido->setElementos($suscripciones);
        PedidoRepository::get_instance()->persist($pedido);
        $_SESSION[$this->cookie] = $pedido->getHash();
        return $pedido;
    }

    public function getByNumber($numero)
    {
        return PedidoRepository::get_instance()->findByNumber($numero);
    }
}
<?php

namespace PronosticosApuestasTAP\Common;

class Pedido
{
    //metodos de pago
    const PAYPAL        = 'PAYPAL';
    const PAYSAFECARD   = 'PAYSAFECARD';
    const SKRILL        = 'SKIRLL';

    //estados del pedido
    const PREPEDIDO = 'PREPEDIDO';
    const PAGADO    = 'PAGADO';
    const CANCELADO = 'CANCELADO';

    /**
     * @var null|\PronosticosApuestasTAP\Common\Pedido
     */
    protected static $instance = null;
    /**
     * @var string
     */
    private $table = 'tapa_pedido';
    /**
     * @var integer
     */
    private $id;
    /**
     * @var string
     */
    private $hash = null;
    /**
     * @var \WP_User
     */
    private $usuario = null;
    /**
     * @var array
     */
    private $elementos = array();
    /**
     * @var integer
     */
    private $fecha = null;
    /**
     * @var string
     */
    private $forma_de_pago;
    /**
     * @var string
     */
    private $estado = null;
    /**
     * @var string
     */
    private $cupon;
    /**
     * @var float
     */
    private $descuento;
    /**
     * @var string
     */
    private $numero;

    public function __construct()
    {
        if(null === $this->fecha){
            $ahora = new \DateTime();
            $this->fecha = $ahora->getTimestamp();
        }
        if(null === $this->estado){
            $this->estado = self::PREPEDIDO;
        }
        if(null === $this->hash){
            $this->hash = sha1((string)$this->fecha);
        }
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId( $id ) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getHash() {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash( $hash ) {
        $this->hash = $hash;
    }

    /**
     * @return \WP_User
     */
    public function getUsuario() {
        return $this->usuario;
    }

    /**
     * @param \WP_User $usuario
     */
    public function setUsuario( \WP_User $usuario = null ) {
        $this->usuario = $usuario;
    }

    /**
     * @return array
     */
    public function getElementos() {
        return $this->elementos;
    }

    /**
     * @param array $elementos
     */
    public function setElementos( $elementos ) {
        $this->elementos = $elementos;
    }

    /**
     * @return int
     */
    public function getFecha() {
        return $this->fecha;
    }

    /**
     * @param int $fecha
     */
    public function setFecha( $fecha ) {
        $this->fecha = $fecha;
    }

    /**
     * @return string
     */
    public function getFormaDePago() {
        return $this->forma_de_pago;
    }

    /**
     * @param string $forma_de_pago
     */
    public function setFormaDePago( $forma_de_pago ) {
        $this->forma_de_pago = $forma_de_pago;
    }

    /**
     * @return string
     */
    public function getEstado() {
        return $this->estado;
    }

    /**
     * @param string $estado
     */
    public function setEstado( $estado ) {
        $this->estado = $estado;
    }

    /**
     * @return string
     */
    public function getCupon() {
        return $this->cupon;
    }

    /**
     * @param string $cupon
     */
    public function setCupon( $cupon ) {
        $this->cupon = $cupon;
    }

    /**
     * @return float
     */
    public function getDescuento()
    {
        return $this->descuento;
    }

    /**
     * @param float $descuento
     */
    public function setDescuento( $descuento )
    {
        $this->descuento = $descuento;
    }

    /**
     * @return string
     */
    public function getNumero() {
        return $this->numero;
    }

    /**
     * @param string $numero
     */
    public function setNumero( $numero ) {
        $this->numero = $numero;
    }

    /**
     * @return float
     */
    public function getSubtotal()
    {
        $subtotal = 0;
        foreach ( $this->elementos as $elemento ) {
            $subtotal += $subtotal + floatval($elemento['precio']);
        }
        return $subtotal;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return floatval($this->getSubtotal()) - floatval($this->getDescuento());
    }

    /**
     * @return null|\PronosticosApuestasTAP\Common\Pedido
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
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }
}
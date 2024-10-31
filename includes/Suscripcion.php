<?php
namespace PronosticosApuestasTAP\Common;

class Suscripcion 
{
    //metodos de pago
    const PAYPAL        = 'PAYPAL';
    const PAYSAFECARD   = 'PAYSAFECARD';
    const SKRILL        = 'SKIRLL';

    //estados de la suscripcion
    const ACTIVO    = 'ACTIVO';
    const PENDIENTE = 'PENDIENTE';
    const INACTIVO  = 'INACTIVO';

    /**
     * @var null|\PronosticosApuestasTAP\Common\Suscripcion
     */
    protected static $instance = null;
    /**
     * @var string
     */
    private $table = 'tapa_suscripcion';
    /**
     * @var integer
     */
    private $id;
    /**
     * @var string
     */
    private $numero;
    /**
     * @var \WP_User
     */
    private $usuario = null;
    /**
     * @var integer
     */
    private $tipster;
    /**
     * @var integer
     */
    private $periodo;
    /**
     * @var string
     */
    private $forma_de_pago;
    /**
     * @var string
     */
    private $estado = null;
    /**
     * @var int
     */
    private $fecha_fin;
    /**
     * @var int
     */
    private $created_at;
    /**
     * @var int
     */
    private $updated_at;

    public function __construct()
    {
        if(null === $this->estado){
            $this->estado = self::PENDIENTE;
        }
        $ahora = new \DateTime();
        $this->created_at = $ahora->getTimestamp();
        $this->updated_at = $ahora->getTimestamp();
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
     * @return \WP_User
     */
    public function getUsuario() {
        return $this->usuario;
    }

    /**
     * @param \WP_User $usuario
     */
    public function setUsuario( \WP_User $usuario ) {
        $this->usuario = $usuario;
    }

    /**
     * @return int
     */
    public function getTipster() {
        return $this->tipster;
    }

    /**
     * @param int $tipster
     */
    public function setTipster( $tipster ) {
        $this->tipster = $tipster;
    }

    /**
     * @return int
     */
    public function getPeriodo() {
        return $this->periodo;
    }

    /**
     * @param int $periodo
     */
    public function setPeriodo( $periodo ) {
        $this->periodo = $periodo;
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
     * @return int
     */
    public function getFechaFin() {
        return $this->fecha_fin;
    }

    /**
     * @param int $fecha_fin
     */
    public function setFechaFin( $fecha_fin ) {
        $this->fecha_fin = $fecha_fin;
    }

    /**
     * @return int
     */
    public function getCreatedAt() {
        return $this->created_at;
    }

    /**
     * @param int $created_at
     */
    public function setCreatedAt( $created_at ) {
        $this->created_at = $created_at;
    }

    /**
     * @return int
     */
    public function getUpdatedAt() {
        return $this->updated_at;
    }

    /**
     * @param int $updated_at
     */
    public function setUpdatedAt( $updated_at ) {
        $this->updated_at = $updated_at;
    }

    /**
     * @return null|\PronosticosApuestasTAP\Common\Suscripcion
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
<?php

namespace PronosticosApuestasTAP\Common;

use PronosticosApuestasTAP\Common\UsuarioRepository;

class PedidoRepository extends AbstractRepository
{
    protected function __construct()
    {
        parent::__construct();

        $this->table = Pedido::get_instance()->getTable();
    }

    /**
     * @return null|\PronosticosApuestasTAP\Common\PedidoRepository
     */
    public static function get_instance()
    {
        // If the single instance hasn't been set, set it now.
        if ( !(self::$instance instanceof PedidoRepository) || null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function create_table()
    {
        $query_create_table = "CREATE TABLE IF NOT EXISTS `".$this->table."` (".
                                         "  `id`  bigint NOT NULL AUTO_INCREMENT ,".
                                         "  `hash`  varchar(50) NULL ,".
                                         "  `usuario`  integer NULL ,".
                                         "  `elementos`  longtext NULL ,".
                                         "  `fecha`  integer NULL ,".
                                         "  `forma_de_pago`  varchar(255) NULL ,".
                                         "  `estado`  varchar(255) NULL ,".
                                         "  `cupon`  varchar(255) NULL ,".
                                         "  `descuento`  float NULL ,".
                                         "  `numero`  varchar(255) NULL ,".
                                         "  PRIMARY KEY (`id`),".
                                         "  INDEX `IDX_buscar_pedido_por_hash` (`hash`) USING BTREE ".
                                         ") ENGINE=MyISAM DEFAULT CHARSET=latin1;";
        $this->em->query($query_create_table);
    }

    public function persist(Pedido $pedido)
    {
        $usuario = $pedido->getUsuario()->ID;
        $elementos = serialize($pedido->getElementos());
        $this->em->insert($this->table, array(
                'hash' => $pedido->getHash(),
                'usuario' => $usuario,
                'elementos' => $elementos,
                'fecha' => $pedido->getFecha(),
                'forma_de_pago' => $pedido->getFormaDePago(),
                'estado' => $pedido->getEstado(),
                'cupon' => $pedido->getCupon(),
                'descuento' => $pedido->getDescuento(),
                'numero' => $pedido->getNumero()
            ), array(
                '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%f', '%s'
            )
        );
    }

    public function update(Pedido $pedido)
    {
        $usuario = $pedido->getUsuario()->ID;
        $elementos = serialize($pedido->getElementos());

        $this->em->update($this->table, array(
                'hash' => $pedido->getHash(),
                'usuario' => $usuario,
                'elementos' => $elementos,
                'fecha' => $pedido->getFecha(),
                'forma_de_pago' => $pedido->getFormaDePago(),
                'estado' => $pedido->getEstado(),
                'cupon' => $pedido->getCupon(),
                'descuento' => $pedido->getDescuento(),
                'numero' => $pedido->getNumero()
            ), array(
                'id' => $pedido->getId()
            ), array(
                '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%f', '%s'
            )
        );
    }

    public function remove(Pedido $pedido){
        $this->em->delete($this->table, array(
                'hash' => $pedido->getHash()
            ), array(
                '%s'
            )
        );
    }

    /**
     * @param $cookie
     *
     * @return null|\PronosticosApuestasTAP\Common\Pedido
     */
    public function findByCookie($cookie)
    {
        $query = "SELECT * FROM {$this->table} WHERE hash = '{$cookie}' LIMIT 1";
        $queryResult = $this->em->get_row($query, OBJECT);

        if( null === $queryResult){
            return null;
        }

        $usuario = null;
        $usuario = UsuarioRepository::get_instance()->getUserById(intval($queryResult->usuario));
        if(null === $usuario){
            global $current_user;
            get_currentuserinfo();
            $usuario = $current_user;
        }

        $pedido = new Pedido();
        $pedido->setId($queryResult->id);
        $pedido->setHash($queryResult->hash);
        $pedido->setUsuario($usuario);
        $pedido->setElementos(unserialize($queryResult->elementos));
        $pedido->setEstado($queryResult->estado);
        $pedido->setFecha($queryResult->fecha);
        $pedido->setCupon($queryResult->cupon);
        $pedido->setDescuento($queryResult->descuento);
        $pedido->setNumero($queryResult->numero);

        PedidoRepository::get_instance()->update($pedido);

        return $pedido;
    }

    /**
     * @param $numero
     *
     * @return null|\PronosticosApuestasTAP\Common\Pedido
     * @throws \PronosticosApuestasTAP\Common\NoExisteUsuarioAsociadoPedidoException
     */
    public function findByNumber($numero)
    {
        $query = "SELECT * FROM {$this->table} WHERE numero = {$numero} LIMIT 1;";
        $queryResult = $this->em->get_row($query, OBJECT);

        if( null === $queryResult){
            throw new \Exception(sprintf('No hay pedido registrado asociado al numero "%s"',$numero));
        }

        $usuario = null;
        $usuario = UsuarioRepository::get_instance()->getUserById(intval($queryResult->usuario));
        if(null === $usuario){
            throw new \Exception('No existe un usuario asociado al pedido seleccionado');
        }

        $pedido = new Pedido();
        $pedido->setId($queryResult->id);
        $pedido->setHash($queryResult->hash);
        $pedido->setUsuario($usuario);
        $pedido->setElementos(unserialize($queryResult->elementos));
        $pedido->setEstado($queryResult->estado);
        $pedido->setFecha($queryResult->fecha);
        $pedido->setCupon($queryResult->cupon);
        $pedido->setDescuento($queryResult->descuento);
        $pedido->setNumero($queryResult->numero);

        return $pedido;
    }

    public function findByNumberNotEmpty()
    {
        $query_ignore_suscripciones = ";";
        $suscripciones = SuscripcionRepository::get_instance()->findAllNumbers();
        if(!empty($suscripciones)){
            $query_ignore_suscripciones = " AND numero NOT IN ({$suscripciones});";
        }

        $query = "SELECT * FROM {$this->table} WHERE estado <> 'CANCELADO' AND numero <> '' AND numero IS NOT NULL".$query_ignore_suscripciones;
        $queryResult = $this->em->get_results($query);
        $pedidos = array();
        foreach ( $queryResult as $row ) {
            $usuario = null;
            $usuario = UsuarioRepository::get_instance()->getUserById(intval($row->usuario));
            if(null === $usuario){
                continue;
            }
            $pedido = new Pedido();
            $pedido->setId($row->id);
            $pedido->setHash($row->hash);
            $pedido->setUsuario($usuario);
            $pedido->setElementos(unserialize($row->elementos));
            $pedido->setEstado($row->estado);
            $pedido->setFecha($row->fecha);
            $pedido->setCupon($row->cupon);
            $pedido->setDescuento($row->descuento);
            $pedido->setNumero($row->numero);
            $pedidos[] = $pedido;
        }
        return $pedidos;
    }
}
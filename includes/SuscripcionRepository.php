<?php
namespace PronosticosApuestasTAP\Common;

class SuscripcionRepository extends AbstractRepository
{
    protected function __construct()
    {
        parent::__construct();
        
        $this->table = Suscripcion::get_instance()->getTable();
    }
    
    /**
     * @return null|\PronosticosApuestasTAP\Common\SuscripcionRepository
     */
    public static function get_instance()
    {
        // If the single instance hasn't been set, set it now.
        if ( !(self::$instance instanceof SuscripcionRepository) || null == self::$instance ) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    
    public function create_table()
    {
        $query_create_table = "CREATE TABLE IF NOT EXISTS `".$this->table."` (".
                              "  `id`  bigint NOT NULL AUTO_INCREMENT ,".
                              "  `numero`  varchar(255) NOT NULL ,".
                              "  `usuario`  int(11) NOT NULL ,".
                              "  `tipster`  int(11) NOT NULL ,".
                              "  `periodo`  int(11) NOT NULL ,".
                              "  `forma_de_pago`  varchar(255) NULL ,".
                              "  `estado`  varchar(255) NULL ,".
                              "  `fecha_fin`   int(11) NULL ,".
                              "  `created_at`  int(11) NOT NULL ,".
                              "  `updated_at`  int(11) NOT NULL ,".
                              "  PRIMARY KEY (`id`)".
                              ") ENGINE=MyISAM DEFAULT CHARSET=latin1;";
        $this->em->query($query_create_table);
    }
    
    public function persist(Suscripcion $suscripcion)
    {
        $usuario = $suscripcion->getUsuario()->ID;
        $this->em->insert($this->table, array(
            'numero' => $suscripcion->getNumero(),
            'usuario' => $usuario,
            'tipster' => $suscripcion->getTipster(),
            'periodo' => $suscripcion->getPeriodo(),
            'forma_de_pago' => $suscripcion->getFormaDePago(),
            'estado' => $suscripcion->getEstado(),
            'fecha_fin' => $suscripcion->getFechaFin(),
            'created_at' => $suscripcion->getCreatedAt(),
            'updated_at' => $suscripcion->getUpdatedAt()
        ), array(
                '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%d', '%d'
            )
        );
    }
    
    public function update(Suscripcion $suscripcion)
    {
        $usuario = $suscripcion->getUsuario()->ID;
        $this->em->update($this->table, array(
            'numero' => $suscripcion->getNumero(),
            'usuario' => $usuario,
            'tipster' => $suscripcion->getTipster(),
            'periodo' => $suscripcion->getPeriodo(),
            'forma_de_pago' => $suscripcion->getFormaDePago(),
            'estado' => $suscripcion->getEstado(),
            'fecha_fin' => $suscripcion->getFechaFin(),
            'created_at' => $suscripcion->getCreatedAt(),
            'updated_at' => $suscripcion->getUpdatedAt()
        ), array(
            'id' => $suscripcion->getId()
        ), array(
                '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%d', '%d'
            )
        );
    }

    /**
     * @param \PronosticosApuestasTAP\Common\Suscripcion $suscripcion
     *
     * @return false|int
     */
    public function delete(Suscripcion $suscripcion)
    {
        return $this->em->delete($this->table, array(
                'id' => $suscripcion->getId()
            ),array(
                '%d'
            )
        );
    }

    /**
     * @param $query
     *
     * @return array
     */
    private function getAsArray($query)
    {
        $suscripciones = array();
        $queryResult = $this->em->get_results($query);
        foreach ( $queryResult as $row ) {
            $usuario = get_userdata(intval($row->usuario));
            if(false !== $usuario){ // solo si el usuario existe se devuelve la suscripcion
                $suscripcion = new Suscripcion();
                $suscripcion->setId($row->id);
                $suscripcion->setNumero($row->numero);
                $suscripcion->setUsuario($usuario);
                $suscripcion->setTipster($row->tipster);
                $suscripcion->setPeriodo($row->periodo);
                $suscripcion->setFormaDePago($row->forma_de_pago);
                $suscripcion->setEstado($row->estado);
                $suscripcion->setFechaFin($row->fecha_fin);
                $suscripcion->setCreatedAt($row->created_at);
                $suscripcion->setUpdatedAt($row->updated_at);
                $suscripciones[] = $suscripcion;
            }
        }

        return $suscripciones;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $query = "SELECT * FROM {$this->table};";
        $suscripciones = $this->getAsArray($query);

        return $suscripciones;
    }
    
    public function findById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = {$id};";
        $queryResult = $this->em->get_row($query);
        $usuario = get_user_by('id', $queryResult->usuario);
        $suscripcion = new Suscripcion();
        $suscripcion->setId($queryResult->id);
        $suscripcion->setNumero($queryResult->numero);
        $suscripcion->setUsuario($usuario);
        $suscripcion->setTipster($queryResult->tipster);
        $suscripcion->setPeriodo($queryResult->periodo);
        $suscripcion->setFormaDePago($queryResult->forma_de_pago);
        $suscripcion->setEstado($queryResult->estado);
        $suscripcion->setFechaFin($queryResult->fecha_fin);
        $suscripcion->setCreatedAt($queryResult->created_at);
        $suscripcion->setUpdatedAt($queryResult->updated_at);

        return $suscripcion;
    }

    /**
     * @param $fecha_fin
     * @param $estado
     *
     * @return array
     */
    public function getByFechaFinAndEstado($fecha_fin, $estado)
    {
        $query = "SELECT * FROM {$this->table} WHERE fecha_fin <= {$fecha_fin} AND estado = '{$estado}';";
        $suscripciones = $this->getAsArray($query);
        return $suscripciones;
    }

    /**
     * @param $tipster_id
     * @param $estado
     *
     * @return array
     */
    public function getByTipsterAndEstado($tipster_id, $estado)
    {
        $query = "SELECT * FROM {$this->table} WHERE tipster <= {$tipster_id} AND estado = '{$estado}';";
        $suscripciones = $this->getAsArray($query);
        return $suscripciones;
    }

    /**
     * @return string
     */
    public function findAllNumbers()
    {
        $query = "SELECT numero FROM {$this->table} WHERE `numero` <> '' AND `numero` IS NOT NULL;";
        $queryResult = $this->em->get_results($query);

        $numeros = array();
        foreach ( $queryResult as $row ) {
            $numeros[] = $row->numero;
        }
        $numeros = implode(',',$numeros);

        return $numeros;
    }

    public function getList($start = 0, $limit = 10, $order = array(), $search = '', $filter = array())
    {
        $query = "SELECT * FROM {$this->table}";

        $queryPart = array('where' => false, 'and' => false);

        $querySearch = $this->getQuerySearch($search, $queryPart);

        $queryFilter = $this->getQueryFilter($filter, $querySearch);

        $queryOrder = '';
        if(!empty($order)){
            $queryOrder .= " ORDER BY ";
            $queryOrderColumns = array();
            switch($order['column']) {
                case 0:
                    $queryOrderColumns[] = "numero ".$order['dir'];
                    break;
                case 1:
                    $queryOrderColumns[] = "usuario ".$order['dir'];
                    break;
                case 2:
                    $queryOrderColumns[] = "tipster ".$order['dir'];
                    break;
                case 3:
                    $queryOrderColumns[] = "periodo ".$order['dir'];
                    break;
                case 4:
                    $queryOrderColumns[] = "fecha_fin ".$order['dir'];
                    break;
                case 5:
                    $queryOrderColumns[] = "forma_de_pago ".$order['dir'];
                    break;
                case 6:
                    $queryOrderColumns[] = "estado ".$order['dir'];
                    break;
                default:
                    $queryOrderColumns[] = "id ".$order['dir'];
                    break;
            }
            $queryOrder .= implode(',', $queryOrderColumns);
        }

        $queryLimit = $limit > -1 ? " LIMIT {$start},{$limit}" : '';

        $query .= $querySearch['where'];
        $query .= $queryFilter['and'];
        $query .= $queryOrder;
        $query .= $queryLimit;

        $suscripciones = $this->getAsArray($query);

        return $suscripciones;
    }

    public function getTotalList()
    {
        $query = "SELECT COUNT(id) FROM {$this->table}";
        $queryResult = $this->em->get_var($query);

        return $queryResult;
    }

    public function getTotalFiltered($search = '', $filter = array())
    {
        $query = "SELECT COUNT(id) FROM {$this->table}";

        $queryPart = array('where' => false, 'and' => false);

        $querySearch = $this->getQuerySearch($search, $queryPart);

        $queryFilter = $this->getQueryFilter($filter, $querySearch);

        $query .= $querySearch['where'];
        $query .= $queryFilter['and'];

        $queryResult = $this->em->get_var($query);

        return $queryResult;
    }

    private function getQuerySearch($search = '', $queryPart)
    {
        $querySearch = '';
        if(!empty($search)){
            $querySearch .= " WHERE ";
            $querySearchColumns = array();

            $wild = '%';
            $like = $wild . $this->em->esc_like( $search ) . $wild;

            $querySearchColumns[] = $this->em->prepare("id = %d", $search);
            $querySearchColumns[] = $this->em->prepare("numero LIKE %s", $like);
//            $querySearchColumns[] = esc_sql("usuario = ".$filter);
//            $querySearchColumns[] = esc_sql("tipster = ".$filter);
            $querySearchColumns[] = $this->em->prepare("periodo = %d", $search);
            $querySearchColumns[] = $this->em->prepare("forma_de_pago LIKE %s", $like);
            $querySearchColumns[] = $this->em->prepare("estado LIKE %s", $like);

            $querySearch .= implode(" OR ", $querySearchColumns);
            $queryPart['where'] = $querySearch;
        }else{
            $queryPart['where'] = '';
        }

        return $queryPart;
    }

    private function getQueryFilter( $filter = array(), $queryPart )
    {
        $queryFilter = '';
        if(null !== $filter && (int)$filter['execute'] && ( !empty($filter['fecha_inicio']) || !empty($filter['fecha_fin']) ) )  {
            $op = " AND ";
            if(empty($queryPart['where'])){
                $op = " WHERE ";
            }

            if(!empty($filter['fecha_inicio']) && !empty($filter['fecha_fin'])){
                $fecha_inicio = \DateTime::createFromFormat('d/m/Y', $filter['fecha_inicio'])->getTimestamp();
                $fecha_fin = \DateTime::createFromFormat('d/m/Y', $filter['fecha_fin'])->getTimestamp();
                $queryFilter .= $this->em->prepare($op." ( fecha_fin BETWEEN %d AND %d )", $fecha_inicio, $fecha_fin);
            }elseif(!empty($filter['fecha_inicio']) && empty($filter['fecha_fin'])){
                $fecha_inicio = \DateTime::createFromFormat('d/m/Y', $filter['fecha_inicio'])->getTimestamp();
                $queryFilter .= $this->em->prepare($op." ( fecha_fin >= %d )", $fecha_inicio);
            }elseif(empty($filter['fecha_inicio']) && !empty($filter['fecha_fin'])){
                $fecha_fin = \DateTime::createFromFormat('d/m/Y', $filter['fecha_fin'])->getTimestamp();
                $queryFilter .= $this->em->prepare($op." ( fecha_fin <= %d )", $fecha_fin);
            }

            $queryPart['and'] = $queryFilter;
        }else{
            $queryPart['and'] = '';
        }

        return $queryPart;
    }
}
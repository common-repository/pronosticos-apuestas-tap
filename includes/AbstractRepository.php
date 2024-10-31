<?php

namespace PronosticosApuestasTAP\Common;

abstract class AbstractRepository implements BaseRepositoryInterface
{
    /**
     * @var null|\PronosticosApuestasTAP\Common\BaseRepositoryInterface
     */
    protected static $instance = null;

    /**
     * @var \wpdb
     */
    protected $em;

    /**
     * @var string
     */
    protected $table;

    protected function __construct()
    {
        global $wpdb;
        $this->em = $wpdb;
    }
}
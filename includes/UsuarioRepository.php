<?php

namespace PronosticosApuestasTAP\Common;


class UsuarioRepository extends AbstractRepository
{
    protected function __construct()
    {
        parent::__construct();

        $this->table = $this->em->users;
    }

    /**
     * @return null|\PronosticosApuestasTAP\Common\UsuarioRepository
     */
    public static function get_instance()
    {
        // If the single instance hasn't been set, set it now.
        if ( !(self::$instance instanceof UsuarioRepository) || null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param $userId
     *
     * @return null|\WP_User
     */
    public function getUserById($userId)
    {
        $user = get_user_by( 'id', $userId );

        if($user === false){
            return null;
        }

        return $user;
    }
}
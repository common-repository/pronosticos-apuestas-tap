<?php

namespace PronosticosApuestasTAP\Common;

class TipsterRepository extends AbstractRepository
{
    protected function __construct()
    {
        parent::__construct();

        $this->table = $this->em->posts;
    }

    /**
     * @return null|\PronosticosApuestasTAP\Common\TipsterRepository
     */
    public static function get_instance()
    {
        // If the single instance hasn't been set, set it now.
        if ( !(self::$instance instanceof TipsterRepository) || null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param null $id
     *
     * @return null|\WP_Post
     */
    public function findBy($id = null)
    {
        global $post;
        $tipster = null;
        $query_args = array(
            'post_type' => 'tipster',
            'posts_per_page' => 1,
        );

        if(null !== $id){
            $query_args['p'] = $id;
        }

        $query = new \WP_Query($query_args);
        if($query->have_posts()){
            while($query->have_posts()){
                $query->the_post();
                $tipster = $post;
            }
            wp_reset_postdata();
        }

        return $tipster;
    }
}
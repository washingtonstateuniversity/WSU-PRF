<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_data {
    function __construct() { }

	public function get_options(){
		$plugin_option = get_option('catpdf_options',array(
            'enablecss' => 'off',
            'title' => 'Report %mm-%yyyy',
            'dltemplate' => 'def',
            'postdl' => 'on'
        ));	
		return $plugin_option;
	}

	public function getAllPostTypes(){ }

    /*
     * Get post data
     * @id - int
	 * It's worth noting that any out put here will print into the pdf.  If the PDF can't be 
	 * read then look at it in a text editor like Notepad, where you will see the php errors
     */
    public function query_posts($id = NULL) {
		global $_params;
		$type = isset($_params['type'])?$_params['type']:"post";
        $args = array(
            'post_type' => $type,
            'posts_per_page' => -1,
            'order' => 'DESC'
        );
        if ($id !== NULL) {
            $args['p'] = $id;
        }
        if (isset($_params['user']) && count($_params['user']) > 0) {
            $args['author'] = implode(',',$_params['user']);
        }
        if (isset($_params['status']) && count($_params['status']) > 0) {
			$args['post_status'] = implode(',',$_params['status']);
        }
        if (isset($_params['cat']) && count($_params['cat']) > 0) {
            $args['cat'] = implode(',',$_params['cat']);
        }
        add_filter('posts_where', array( $this, 'filter_where' ));
        $result = new WP_Query($args);
		
        return $result->posts;
    }
    /*
     * Return query filter
     * @where - string
     */
    public function filter_where($where = '') {
        global $_params;
        if (isset($_params['from']) && $_params['from'] != '') {
            $from = date('Y-m-d', strtotime($_params['from']));
            $where .= ' AND DATE_FORMAT( post_date , "%Y-%m-%d" ) >= "' . $from . '"';
        }
        if (isset($_params['to']) && $_params['to'] != '') {
            $to = date('Y-m-d', strtotime($_params['to']));
            $where .= ' AND DATE_FORMAT( post_date , "%Y-%m-%d" ) <= "' . $to . '"';
        }
        return $where;
    }


}
?>
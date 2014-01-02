<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_data {
    function __construct() {
		
    }

	public function get_options(){
		$plugin_option = get_option('catpdf_options',array(
            'enablecss' => 'off',
            'title' => 'Report %mm-%yyyy',
            'dltemplate' => 'def',
            'postdl' => 'on'
        ));	
		return $plugin_option;
	}

	public function getAllPostTypes(){
		
	}

    /*
     * Get post data
     * @id - int
	 * It's worth noting that any out put here will print into the pdf.  If the PDF can't be 
	 * read then look at it in a text editor like Notepad, where you will see the php errors
     */
    public function query_posts($id = NULL) {
		global $_params;
 		$params = $_params;
		$type = isset($params['type'])?$params['type']:"post";
        $args = array(
            'post_type' => $type,
            'posts_per_page' => -1,
            'order' => 'DESC'
        );
        if ($id !== NULL) {
            $args['p'] = $id;
        }
        if (isset($params['user']) && count($params['user']) > 0) {
            $au_str = '';
            foreach ($params['user'] as $au) {
                $au_str .= $au . ',';
            }
            $args['author'] = substr($au_str, 0, -1);
        }
        if (isset($params['status']) && count($params['status']) > 0) {
            $status_str = '';
            foreach ($params['status'] as $status) {
                $status_str .= $status . ',';
            }
            $args['post_status'] = substr($status_str, 0, -1);
        }
        if (isset($params['cat']) && count($params['cat']) > 0) {
            $cat_str = '';
            foreach ($params['cat'] as $cat) {
                $cat_str .= $cat . ',';
            }
            $args['cat'] = substr($cat_str, 0, -1);
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
 		$params = $_params;
        if (isset($params['from']) && $params['from'] != '') {
            $from = date('Y-m-d', strtotime($params['from']));
            $where .= ' AND DATE_FORMAT( post_date , "%Y-%m-%d" ) >= "' . $from . '"';
        }
        if (isset($params['to']) && $params['to'] != '') {
            $to = date('Y-m-d', strtotime($params['to']));
            $where .= ' AND DATE_FORMAT( post_date , "%Y-%m-%d" ) <= "' . $to . '"';
        }
        return $where;
    }


}
?>
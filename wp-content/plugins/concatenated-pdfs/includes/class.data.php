<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_data {
    function __construct() { }


	public function get_options(){
		
		$plugin_option_defaults=array(
				'concat' => array(
								'enablecss' => 1,
								'title' => 'Report %mm-%yyyy',
								'dltemplate' => 'default',
								'postdl' => 1,
								'customcss' => ' '
							),
				'single' => array(
								'enablecss' => 1,
								'title' => 'Report %mm-%yyyy',
								'dltemplate' => 'default',
								'postdl' => 1,
								'customcss' => ' '
							),
				"DOMPDF_UNICODE_ENABLED"=>true,
				"DOMPDF_ENABLE_FONTSUBSETTING"=>false,
				"DOMPDF_PDF_BACKEND"=>"CPDF",
				"DOMPDF_DEFAULT_MEDIA_TYPE"=>"screen",
				"DOMPDF_DEFAULT_PAPER_SIZE"=>"letter",
				"DOMPDF_DEFAULT_FONT"=>"serif",
				"DOMPDF_DPI"=>96,
				"DOMPDF_ENABLE_PHP"=>true,
				"DOMPDF_ENABLE_JAVASCRIPT"=>true,
				"DOMPDF_ENABLE_REMOTE"=> true,
				"DOMPDF_FONT_HEIGHT_RATIO"=>1.1,
				"DOMPDF_ENABLE_CSS_FLOAT"=>false,

				"DOMPDF_ENABLE_HTML5PARSER"=>true,
				"_dompdf_show_warnings" => false,
				"_dompdf_debug" => false,
				'DEBUGPNG'=>false,
				'DEBUGKEEPTEMP'=>false,
				'DEBUGCSS'=>false,
				'DEBUG_LAYOUT'=>false,
				'DEBUG_LAYOUT_LINES'=>false,
				'DEBUG_LAYOUT_BLOCKS'=>false,
				'DEBUG_LAYOUT_INLINE'=>false,
				'DEBUG_LAYOUT_PADDINGBOX'=>false
			);
		
		$plugin_option = (array)json_decode(get_option('catpdf_options',null));	
		if($plugin_option!=null){
			
			$plugin_option['concat']=(array)$plugin_option['concat'];
			$plugin_option['single']=(array)$plugin_option['single'];
			//var_dump($plugin_option);
			$plugin_option=array_merge($plugin_option_defaults,$plugin_option);
		}else{
			$plugin_option=$plugin_option_defaults;
		}
		return $plugin_option;
	}
	/**
	* Dimensions of paper sizes in points
	*
	* @var array;
	*/
	public $paper_sizes = array(
		"4a0" => array(0,0,4767.87,6740.79),
		"2a0" => array(0,0,3370.39,4767.87),
		"a0" => array(0,0,2383.94,3370.39),
		"a1" => array(0,0,1683.78,2383.94),
		"a2" => array(0,0,1190.55,1683.78),
		"a3" => array(0,0,841.89,1190.55),
		"a4" => array(0,0,595.28,841.89),
		"a5" => array(0,0,419.53,595.28),
		"a6" => array(0,0,297.64,419.53),
		"a7" => array(0,0,209.76,297.64),
		"a8" => array(0,0,147.40,209.76),
		"a9" => array(0,0,104.88,147.40),
		"a10" => array(0,0,73.70,104.88),
		"b0" => array(0,0,2834.65,4008.19),
		"b1" => array(0,0,2004.09,2834.65),
		"b2" => array(0,0,1417.32,2004.09),
		"b3" => array(0,0,1000.63,1417.32),
		"b4" => array(0,0,708.66,1000.63),
		"b5" => array(0,0,498.90,708.66),
		"b6" => array(0,0,354.33,498.90),
		"b7" => array(0,0,249.45,354.33),
		"b8" => array(0,0,175.75,249.45),
		"b9" => array(0,0,124.72,175.75),
		"b10" => array(0,0,87.87,124.72),
		"c0" => array(0,0,2599.37,3676.54),
		"c1" => array(0,0,1836.85,2599.37),
		"c2" => array(0,0,1298.27,1836.85),
		"c3" => array(0,0,918.43,1298.27),
		"c4" => array(0,0,649.13,918.43),
		"c5" => array(0,0,459.21,649.13),
		"c6" => array(0,0,323.15,459.21),
		"c7" => array(0,0,229.61,323.15),
		"c8" => array(0,0,161.57,229.61),
		"c9" => array(0,0,113.39,161.57),
		"c10" => array(0,0,79.37,113.39),
		"ra0" => array(0,0,2437.80,3458.27),
		"ra1" => array(0,0,1729.13,2437.80),
		"ra2" => array(0,0,1218.90,1729.13),
		"ra3" => array(0,0,864.57,1218.90),
		"ra4" => array(0,0,609.45,864.57),
		"sra0" => array(0,0,2551.18,3628.35),
		"sra1" => array(0,0,1814.17,2551.18),
		"sra2" => array(0,0,1275.59,1814.17),
		"sra3" => array(0,0,907.09,1275.59),
		"sra4" => array(0,0,637.80,907.09),
		"letter" => array(0,0,612.00,792.00),
		"legal" => array(0,0,612.00,1008.00),
		"ledger" => array(0,0,1224.00, 792.00),
		"tabloid" => array(0,0,792.00, 1224.00),
		"executive" => array(0,0,521.86,756.00),
		"folio" => array(0,0,612.00,936.00),
		"commercial #10 envelope" => array(0,0,684,297),
		"catalog #10 1/2 envelope" => array(0,0,648,864),
		"8.5x11" => array(0,0,612.00,792.00),
		"8.5x14" => array(0,0,612.00,1008.0),
		"11x17"  => array(0,0,792.00, 1224.00),
	);
	public function getAllPostTypes(){ }

    /*
     * Get post data
     * @id - int
	 * It's worth noting that any out put here will print into the pdf.  If the PDF can't be 
	 * read then look at it in a text editor like Notepad, where you will see the php errors
	 * 
     */
    public function query_posts($id = NULL) {
		global $_params;
		$type = isset($_params['type'])?$_params['type']:"post";

		$args = array(
			'posts_per_page'   => 5,
			'offset'           => 0,
			//'category'         => '',
			//'category_name'    => '',
			'orderby'          => 'post_date',
			'order'            => 'DESC',
			'include'          => '',
			//'exclude'          => '',
			//'meta_key'         => '',
			//'meta_value'       => '',
			//'post_type'        => 'post',
			//'post_mime_type'   => '',
			//'post_parent'      => '',
			//'suppress_filters' => true 
		);
				
		// $args['post_type']=$type;
		 $args['posts_per_page']=-1;
		 $args['order']='DESC';
		 
		
        if ($id !== NULL) {
            $args['include'] = $id;
        }
        if (isset($_params['user']) && count($_params['user']) > 0) {
            //$args['author'] = implode(',',$_params['user']);
        }
        if (isset($_params['status']) && count($_params['status']) > 0) {
			$args['post_status'] = implode(',',$_params['status']);
        }else{
			$args['post_status']= implode(',',array_keys(get_post_statuses()));	
		}
        if (isset($_params['cat']) && count($_params['cat']) > 0) {
            //$args['cat'] = implode(',',$_params['cat']);
        }

		//var_dump($args);
		//var_dump(get_posts( array('include'=>array($id))));

		$posts_array = get_posts( $args );
		
		
		
		
        //$result = new WP_Query($args);
		
        return $posts_array;//$result->posts;
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
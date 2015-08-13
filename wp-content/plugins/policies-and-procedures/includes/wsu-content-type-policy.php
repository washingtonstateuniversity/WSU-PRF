<?php
/**
 * Provide a content type to handle policies.
 *
 * Class WSU_Content_Type_Policy
 */
class WSU_Content_Type_Policy {

	/**
	 * @var string The slug to register the policy post type under.
	 */
	var $post_type = 'wsu_policy';

	/**
	 * @var string The URL slug to use for a single policy.
	 */
	var $post_type_slug = 'policy';

	/**
	 * @var string The general name used for the post type.
	 */
	var $post_type_name = 'Policies And Procedures';

	/**
	 * @var string The slug used for archives.
	 */
	var $post_type_archive = 'policies';

	/**
	 * Set up the hooks used by WSU_Content_Type_Policy
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'generate_rewrite_rules', array( $this, 'rewrite_rules' ) );
		add_action( 'pre_get_posts', array( $this, 'modify_post_query' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_filter( 'post_row_actions', array( $this, 'policy_post_action_row' ), 10, 2);
		add_action( 'save_post', array( $this, 'save_meta' ), 15, 2 );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		add_action( 'manage_'.$this->post_type.'_posts_custom_column', array( $this, 'manage_list_table_number_column' ), 10, 2 );
		add_action( 'manage_'.$this->post_type.'_posts_custom_column', array( $this, 'manage_list_table_policy_dates_column' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'my_policy_number_orderby' ) );

		add_filter( 'wpseo_title', array( $this, 'post_type_archive_wpseo_title'), 10, 1 );
		add_filter( 'post_type_archive_title', array( $this, 'post_type_archive_title' ), 10, 1 );
		add_filter( 'manage_edit-' . $this->post_type . '_columns', array( $this, 'manage_list_table_columns' ), 10, 1 );
		add_filter( 'manage_edit-' . $this->post_type . '_sortable_columns', array( $this, 'my_sortable_policy_column' ) );

	}

	/**
	 * Register the Policy post type for PRF.
	 *
	 * Single policy item: http://policies.wsu.edu/policy/single-title-slug/
	 * Policy archives:    http://policies.wsu.edu/policies/
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => $this->post_type_name,
			'singular_name'      => 'Policy',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Policy',
			'edit_item'          => 'Edit Policy',
			'new_item'           => 'New Policy',
			'all_items'          => 'All Policies',
			'view_item'          => 'View Policy',
			'search_items'       => 'Search Policies',
			'not_found'          => 'No policies found',
			'not_found_in_trash' => 'No policies found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Policies',
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $this->post_type_slug ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => true,
			'menu_position'      => 5,
			'supports'           => array('title','editor','categories','excerpt','custom-fields','revisions','page-attributes'),
			'taxonomies'         => array( 'category', 'post_tag' ),
		);

		register_post_type( $this->post_type, $args );
	}

	/**
	 * Add meta boxes used in the policy edit screen.
	 */
	public function add_meta_boxes() {
		add_meta_box( 'wsu_policy_number', 'Policy Number:', array( $this, 'display_number_meta_box' ), $this->post_type, 'side' );
		add_meta_box( 'wsu_policy_date', 'Policy Dates:', array( $this, 'display_date_meta_box' ), $this->post_type, 'side' );
	}

	/**
	 * Enqueue scripts and styles required in the admin.
	 */
	public function enqueue_admin_scripts() {
		if ( $this->post_type === get_current_screen()->id ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-core', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css' );
			wp_enqueue_script( 'wsu-policy-maskedinput', PNP_URL.'/js/jquery.maskedinput.js', array(), false, true );
			wp_enqueue_script( 'wsu-policy-admin', PNP_URL.'/js/policies-admin.js', array(), false, true );
			
		}
	}

	/**
	 * Display the email associated with the policy submission.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function display_number_meta_box( $post ) {
		$postmeta = get_post_meta( $post->ID, '_wsu_policy_number', true );
		
		$parent_policy_num="";
		$parents = get_post_ancestors( $post->ID );
		foreach($parents as $parent_id){
			$policy_num = get_post_meta( $parent_id, '_wsu_policy_number', true );
			if($policy_num!=""){
				$parent_policy_num.= (!empty($parent_policy_num)?".":"").$policy_num;
			}
		}
		
		$text = isset( $postmeta ) ? esc_attr( $postmeta ) : ''; 
		$_note = __('Policy reference number');
		$_input = $parent_policy_num.(!empty($parents)?'.':'').'<input type="text" id="wsu_policy_number" class="policy-form-input" name="wsu_policy_number" value="'.$text.'" /><p>'.$_note.'</p>';
		echo $_input;
	}

	/**
	 * Display the contact dates associated with the policy submission.
	 *
	 * @param WP_Post $post Post object to display meta for.
	 */
	public function display_date_meta_box( $post ) {
		$postmeta = get_post_meta( $post->ID, '_wsu_policy_date', true );
		$text = isset( $postmeta ) ? esc_attr( $postmeta ) : ''; 
		$_note = __('This is the Date the policy went into effect.');
		$_input = '<input type="date" id="wsu_policy_date" class="policy-form-input" name="wsu_policy_date" value="'.$text.'" /><p>'.$_note.'</p>';
		echo $_input;
		
	}

	public function policy_post_action_row($actions, $post){
		//check for your post type
		if ($post->post_type == $this->post_type ){
			//
		}
		return $actions;
	}

	/**
	 * Modify rewrite rules to include support for additional requirements.
	 *
	 * We primarily want to add support for date based archives to policies. This may
	 * involve some trickery as our true date information is stored in post meta and will
	 * not use the standard day/month/year data passed to us.
	 *
	 * @param WP_Rewrite $wp_rewrite Existing rewrite rules.
	 *
	 * @return WP_Rewrite Modified set of rewrite rules.
	 */
	public function rewrite_rules( $wp_rewrite ) {
		$rules = array();

		$dates = array(
			array(
				'rule' => "([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})",
				'vars' => array( 'year', 'monthnum', 'day' ) ),
			array(
				'rule' => "([0-9]{4})/([0-9]{1,2})",
				'vars' => array( 'year', 'monthnum' ) ),
			array(
				'rule' => "([0-9]{4})",
				'vars' => array( 'year' ) ),
		);

		foreach ( $dates as $data ) {
			$query = 'index.php?post_type=' . $this->post_type;
			$rule = $this->post_type_archive . '/' . $data['rule'];

			$i = 1;
			foreach ( $data['vars'] as $var ) {
				$query .= '&' . $var . '=' . $wp_rewrite->preg_index( $i );
				$i++;
			}

			$rules[ $rule . "/?$"                               ] = $query;
			$rules[ $rule . "/feed/(feed|rdf|rss|rss2|atom)/?$" ] = $query . "&feed="  . $wp_rewrite->preg_index( $i );
			$rules[ $rule . "/(feed|rdf|rss|rss2|atom)/?$"      ] = $query . "&feed="  . $wp_rewrite->preg_index( $i );
			$rules[ $rule . "/page/([0-9]{1,})/?$"              ] = $query . "&paged=" . $wp_rewrite->preg_index( $i );
		}

		$wp_rewrite->rules = $rules + $wp_rewrite->rules;

		return $wp_rewrite;
	}

	/**
	 * Modify the post query to load posts based on our custom date meta.
	 *
	 * @param WP_Query $query The query object currently in progress.
	 */
	public function modify_post_query( $query ) {

		if ( is_admin() || ! is_post_type_archive( $this->post_type ) )
			return;

		if ( ! $query->is_main_query() )
			return;

		// Not to much of an archive if we don't have the year.
		if ( ! isset( $query->query['year'] ) )
			return;

		$query_date = $query->query['year'];
		$query->set( 'year', '' );

		if ( isset( $query->query['monthnum'] ) ) {
			$query_date .= $query->query['monthnum'];
			$query->set( 'monthnum', '' );
		}

		if ( isset( $query->query['day'] ) ) {
			$query_date .= zeroise( $query->query['day'], 2 );
			$query->set( 'day', '' );
			$query->set( 'posts_per_page', 50 ); // Try to fit all of one day's policies on a screen.
		}

		$query->set( 'meta_query', array(
				array(
					'key' => '_policy_date_' . $query_date,
					'value' => 1,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		);

	}


	/**
	 * Modify the WSU Policies post type archive title to properly show date information.
	 *
	 * @param string $name Current title for the archive.
	 *
	 * @return string Modified title for the archive.
	 */
	public function post_type_archive_title( $name ) {

		if ( 'Policies' !== $name ){
			return $name;
		}

		// Get the date from our URL because we've tricked the query until now.
		$url_dates = explode( '/', trim( $_SERVER['REQUEST_URI'], '/' ) );
		array_shift( $url_dates );

		if ( isset( $url_dates[2] ) ){
			return date( 'F j, Y ', strtotime( $url_dates[0] . '-' . $url_dates[1] . '-' . $url_dates[2] . ' 00:00:00' ) ) . $name;
		}elseif ( isset( $url_dates[1] ) && 0 !== absint( $url_dates[0] ) ){
			return date( 'F Y ', strtotime( $url_dates[0] . '-' .  $url_dates[1] . '-01 00:00:00' ) ) . $name;
		}elseif ( isset( $url_dates[0] ) && 0 !== absint( $url_dates[0] ) ){
			return date( 'Y ', strtotime( $url_dates[0] . '-01-01 00:00:00' ) ) . $name;
		}else{
			return $name;
		}

	}

	/**
	 * Filter the WordPress SEO generate post type archive title for Policies.
	 *
	 * @param string $title Title as previously modified by WordPress SEO
	 *
	 * @return string Our replacement version of the title.
	 */
	public function post_type_archive_wpseo_title( $title ) {
		if ( is_post_type_archive( $this->post_type ) ){
			return $this->post_type_archive_title( $this->post_type_name ) . ' |';
		}

		return $title;
	}

	/**
	 * Modify the columns in the post type list table.
	 *
	 * @param array $columns Current list of columns and their names.
	 *
	 * @return array Modified list of columns.
	 */
	public function manage_list_table_columns( $columns ) {
		// unset categories and tags on this screen.
		unset( $columns['categories'] );
		unset( $columns['tags'] );
		unset( $columns['date'] );

		// Add our custom columns. Move date to the end of the array after we unset it above.
		$columns['policy_number'] = 'Policy Number';
		$columns['effictive_dates'] = 'Effictive Date';
		$columns['date'] = 'Publish Date';

		return $columns;
	}

	/**
	 * Handle output for the contact email column in the policy list table.
	 *
	 * @param string $column_name Current column being displayed.
	 * @param int    $post_id     Post ID of the current row being displayed.
	 */
	public function manage_list_table_number_column( $column_name, $post_id ) {
		if ( 'policy_number' !== $column_name ){
			return;
		}
		$parent_policy_num="";
		$parents = get_post_ancestors( $post_id );
		foreach($parents as $parent_id){
			$policy_num = get_post_meta( $parent_id, '_wsu_policy_number', true );
			if($policy_num!=""){
				$parent_policy_num.= (!empty($parent_policy_num)?".":"").$policy_num;
			}
		}
		$number = get_post_meta( $post_id, '_wsu_policy_number', true );
		echo $parent_policy_num.(!empty($parents)?'.':'').esc_html( $number );
	}


	public function my_sortable_policy_column( $columns ) {
		$columns['policy_number'] = '_wsu_policy_number';

		//To make a column 'un-sortable' remove it from the array
		//unset($columns['date']);

		return $columns;
	}
	
	public function my_policy_number_orderby( $query ) {
		if( ! is_admin() ){
			return;
		}

		$orderby = $query->get( 'orderby');

		if( 'policy_number' == $orderby ) {
			$query->set('meta_key','_wsu_policy_number');
			$query->set('orderby','meta_value_num');
		}
	}

	/**
	 * Handle output for the policy dates column in the policy list table.
	 *
	 * @param string $column_name Current column being displayed.
	 * @param int    $post_id     Post ID of the current row being displayed.
	 */
	public function manage_list_table_policy_dates_column( $column_name, $post_id ) {
		if ( 'effictive_dates' !== $column_name ){
			return;
		}

		if ( $number = get_post_meta( $post_id, '_wsu_policy_date', true ) ) {
			echo esc_html( $number );
		}
	}

	/**
	 * Generate a link to a day's policy archives.
	 *
	 * @param string $year  Year to be included in the URL.
	 * @param string $month Month to be included in the URL.
	 * @param string $day   Day to be included in the URL.
	 *
	 * @return string Day's policy URL.
	 */
	public function get_day_link( $year, $month, $day ) {
		return site_url( $this->post_type_archive . '/' . $year . '/' . $month . '/' . $day . '/' );
	}

	/**
	 * Generate a link to a month's policy archives.
	 *
	 * @param string $year  Year to be included in the URL.
	 * @param string $month Month to be included in the URL.
	 *
	 * @return string Month's policy URL.
	 */
	public function get_month_link( $year, $month ) {
		return site_url( $this->post_type_archive . '/' . $year . '/' . $month . '/' );
	}

	public function save_meta( $post_id, $post ) {
		if ( isset( $_POST['wsu_policy_number'] ) ) {
			if ( empty( trim( $_POST['wsu_policy_number'] ) ) ) {
				delete_post_meta( $post_id, '_wsu_policy_number');
			} else {
				update_post_meta( $post_id, '_wsu_policy_number', $_POST['wsu_policy_number'] );
			}
		}
		if ( isset( $_POST['wsu_policy_date'] ) ) {
			if ( empty( trim( $_POST['wsu_policy_date'] ) ) ) {
				delete_post_meta( $post_id, '_wsu_policy_date');
			} else {
				update_post_meta( $post_id, '_wsu_policy_date', $_POST['wsu_policy_date'] );
			}
		}
		return;
	}
	
}
$wsu_content_type_policy = new WSU_Content_Type_Policy();
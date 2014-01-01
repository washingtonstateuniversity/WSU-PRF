<?php
/**
 * Provide a content type to handle policies separately from news
 *
 * Class WSU_Content_Type_Policie
 */
class WSU_Content_Type_Policie {

	/**
	 * @var string The slug to register the policie post type under.
	 */
	var $post_type = 'wsu_policie';

	/**
	 * @var string The URL slug to use for a single policie.
	 */
	var $post_type_slug = 'policie';

	/**
	 * @var string The general name used for the post type.
	 */
	var $post_type_name = 'Policies And Procedures';

	/**
	 * @var string The slug to use for policie archives.
	 */
	var $post_type_archive = 'policies';

	/**
	 * @var string Key used for storing the policie calendar in cache.
	 */
	var $calendar_cache_key = 'wsu_policie_calendar';

	/**
	 * Set up the hooks used by WSU_Content_Type_Policie
	 */
	public function __construct() {
		add_action( 'init',                               array( $this, 'register_post_type'       )        );
		add_action( 'wp_ajax_submit_policie',             array( $this, 'ajax_callback'            )        );
		add_action( 'wp_ajax_nopriv_submit_policie',      array( $this, 'ajax_callback'            )        );
		add_action( 'generate_rewrite_rules',             array( $this, 'rewrite_rules'            )        );
		add_action( 'pre_get_posts',                      array( $this, 'modify_post_query'        )        );
		add_action( 'add_meta_boxes',                     array( $this, 'add_meta_boxes'           )        );
		add_action( 'widgets_init',                       array( $this, 'register_widget'          )        );
		add_action( 'save_post',                          array( $this, 'delete_calendar_cache'    ), 20, 1 );
		add_action( 'delete_post',                        array( $this, 'delete_calendar_cache'    ), 20, 1 );
		add_action( 'update_option_start_of_week',        array( $this, 'delete_calendar_cache'    )        );
		add_action( 'update_option_gmt_offset',           array( $this, 'delete_calendar_cache'    )        );
		add_action( 'save_post',                          array( $this, 'save_policie_dates'  ), 10, 2 );
		add_action( 'admin_enqueue_scripts',              array( $this, 'enqueue_admin_scripts'    )        );

		add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'manage_list_table_email_column'              ), 10, 2 );
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'manage_list_table_policie_dates_column' ), 10, 2 );

		add_filter( 'wpseo_title',                                  array( $this, 'post_type_archive_wpseo_title'), 10, 1 );
		add_filter( 'post_type_archive_title',                      array( $this, 'post_type_archive_title'      ), 10, 1 );
		add_filter( 'manage_edit-' . $this->post_type . '_columns', array( $this, 'manage_list_table_columns'    ), 10, 1 );

		add_shortcode( 'wsu_policie_form',           array( $this, 'output_policie_form' ) );
	}

	/**
	 * Register the Policie post type for the WSU News system.
	 *
	 * Single policie item: http://news.wsu.edu/policie/single-title-slug/
	 * Policie archives:    http://news.wsu.edu/policies/
	 */
	function register_post_type() {
		$labels = array(
			'name'               => $this->post_type_name,
			'singular_name'      => 'Policie',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Policie',
			'edit_item'          => 'Edit Policie',
			'new_item'           => 'New Policie',
			'all_items'          => 'All Policies',
			'view_item'          => 'View Policie',
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
			'has_archive'        => $this->post_type_archive,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'supports'           => array( 'title', 'editor', 'categories' ),
			'taxonomies'         => array( 'category', 'post_tag' ),
		);

		register_post_type( $this->post_type, $args );
	}

	/**
	 * Add meta boxes used in the policie edit screen.
	 */
	function add_meta_boxes() {
		add_meta_box( 'wsu_policie_email', 'Policie Submitted By:', array( $this, 'display_email_meta_box' ), $this->post_type, 'side' );
		add_meta_box( 'wsu_policie_dates', 'Policie Dates:',        array( $this, 'display_dates_meta_box' ), $this->post_type, 'side' );
	}

	/**
	 * Enqueue scripts and styles required in the admin.
	 */
	public function enqueue_admin_scripts() {
		if ( $this->post_type === get_current_screen()->id ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-core', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css' );
			wp_enqueue_script( 'wsu-news-policie-admin', plugins_url( 'wsu-news-policies/js/policies-admin.js' ), array(), false, true );
		}
	}

	/**
	 * Display the email associated with the policie submission.
	 *
	 * @param WP_Post $post Current post object.
	 */
	function display_email_meta_box( $post ) {
		$email = get_post_meta( $post->ID, '_policie_contact_email', true );

		if ( ! $email )
			echo '<strong>No email submitted with policie.';
		else
			echo esc_html( $email );
	}

	/**
	 * Display the contact dates associated with the policie submission.
	 *
	 * @param WP_Post $post Post object to display meta for.
	 */
	function display_dates_meta_box( $post ) {
		$results = $this->_get_policie_date_meta( $post->ID );
		$date_input = '';
		$date_input_count = 1;
		$archive_dates = array( 'daily', 'monthly', 'yearly' );
		
			foreach ( $results as $result ) {
				$date = str_replace( '_policie_date_', '', $result->meta_key );
	
				if ( 4 == strlen( $date ) ) {
					$archive_dates['yearly'][] = '<a href="' . esc_url( site_url( $this->post_type_archive . '/' . $date ) ) . '" >' . $date . '</a>';
				}
	
				if ( 6 == strlen( $date ) ) {
					$date_url = substr( $date, 0, 4 ) . '/' . substr( $date, 4, 2 );
					$date_display = substr( $date, 4, 2 ) . '/' . substr( $date, 0, 4 );
					$archive_dates['monthly'][] = '<a href="' . esc_url( site_url( $this->post_type_archive . '/' . $date_url ) ) . '" >' . $date_display . '</a>';
				}
	
				if ( 8 == strlen( $date ) ) {
					$date_url = substr( $date, 0, 4 ) . '/' . substr( $date, 4, 2 ) . '/' . substr( $date, 6, 2 );
					$date_display = substr( $date, 4, 2 ) . '/' . substr( $date, 6, 2 ) . '/' . substr( $date, 0, 4 );
					$archive_dates['daily'][] = '<a href="' . esc_url( site_url( $this->post_type_archive . '/' . $date_url ) ) . '" >' . $date_display . '</a>';
					$date_input .= '<input type="text" id="policie-form-date' . $date_input_count . '" class="policie-form-input policie-form-date-input" name="policie-date[]" value="' . $date_display . '" />';
					$date_input_count++;
				}
			}
	
			// Ensure we have 3 inputs listed. (This could be expandable...)
			while ( $date_input_count <= 3 ) {
				$date_input .= '<input type="text" id="policie-form-date' . $date_input_count . '" class="policie-form-input policie-form-date-input" name="policie-date[]" value="" />';
				$date_input_count++;
			}
		
		
		?>
		<label for="policie-form-date">This policie is assigned to the following date(s):</label><br /><br />
		<?php echo $date_input;	?>
		<p>It will appear on the following policie archive pages:</p>
		<ul>
			<li>Yearly: <?php echo (isset($archive_dates['yearly'])?implode( ', ', $archive_dates['yearly'] ):""); ?></li>
			<li>Monthly: <?php echo (isset($archive_dates['yearly'])?implode( ', ', $archive_dates['monthly'] ):""); ?></li>
			<li>Daily: <?php echo (isset($archive_dates['yearly'])?implode( ', ', $archive_dates['daily'] ):""); ?></li>
		</ul>
		<?php
		if(!empty($results)){
		}else{
			//echo "Once you have saved a policie then you may archive it.";	
			
		}
		
	}

	/**
	 * Save the dates assigned to an policie whenever an policie is updated.
	 *
	 * @param $post_id
	 * @param $post
	 */
	public function save_policie_dates( $post_id, $post ) {
		if ( $this->post_type !== $post->post_type ) {
			return;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['policie-date'] ) ) {
			return;
		}

		$formatted_dates = array();
		foreach( $_POST['policie-date'] as $date ) {
			$formatted_dates[] = strtotime( $date );
		}
		sort( $formatted_dates );

		$this->_clear_policie_date_meta( $post_id );
		$this->_save_policie_date_meta( $post_id, $formatted_dates );

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
	function rewrite_rules( $wp_rewrite ) {
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
	function modify_post_query( $query ) {

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
					'key' => '_policie_date_' . $query_date,
					'value' => 1,
					'compare' => '=',
					'type' => 'numeric'
				)
			)
		);

	}

	/**
	 * Setup the policie form for output when the shortcode is used.
	 *
	 * @return string Contains form to be output.
	 */
	function output_policie_form() {
		// Enqueue jQuery UI's datepicker to provide an interface for the publish date(s).
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-core', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css' );

		// Enqueue the Javascript needed to handle the form submission properly.
		wp_enqueue_script( 'wsu-news-policie-form', plugins_url( 'wsu-news-policies/js/policies-form.js' ), array(), false, true );

		// Provide a global variable containing the ajax URL that we can access
		wp_localize_script( 'wsu-news-policie-form', 'policieSubmission', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_style( 'wsu-news-policie-form', plugins_url( 'wsu-news-policies/css/policies-form.css' ) );

		// Build the output to return for use by the shortcode.
		ob_start();
		?>
		<div id="policie-submission-form" class="policie-form" action="">
			<form action="#">
				<label for="policie-form-title">Policie Title:</label>
				<input type="text" id="policie-form-title" class="policie-form-input" name="policie-title" value="" />
				<label for="policie-form-text">Policie Text:</label>
				<?php
				$editor_settings = array(
					'wpautop'       => true,
					'media_buttons' => false,
					'textarea_name' => 'policie-text',
					'textarea_rows' => 15,
					'editor_class'  => 'policie-form-input',
					'teeny'         => false,
					'dfw'           => false,
					'tinymce'       => array(
						'theme_advanced_disable' => 'wp_more, fullscreen, wp_help',
					),
					'quicktags'     => false,
				);
				wp_editor( '', 'policie-form-text', $editor_settings );
				?>
				<label for="policie-form-date">What date(s) should this policie be published on?</label><br>
				<input type="text" id="policie-form-date1" class="policie-form-input policie-form-date-input" name="policie-date[]" value="" />
				<input type="text" id="policie-form-date2" class="policie-form-input policie-form-date-input" name="policie-date[]" value="" />
				<input type="text" id="policie-form-date3" class="policie-form-input policie-form-date-input" name="policie-date[]" value="" />
				<br>
				<br>
				<label for="policie-form-email">Your Email Address:</label><br>
				<input type="text" id="policie-form-email" class="policie-form-input" name="policie-email" value="" />
				<div id="policie-other-wrap">
					If you see the following input box, please leave it empty.
					<label for="policie-form-other">Other Input:</label>
					<input type="text" id="policie-form-other" class="policie-form-input" name="policie-other" value="" />
				</div>
				<input type="submit" id="policie-form-submit" class="policie-form-input" value="Submit Policie" />
			</form>
		</div>
		<?php
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Handle the ajax submission of the policie form.
	 */
	function ajax_callback() {
		if ( ! DOING_AJAX || ! isset( $_POST['action'] ) || 'submit_policie' !== $_POST['action'] )
			die();

		// If the honeypot input has anything filled in, we can bail.
		if ( isset( $_POST['other'] ) && '' !== $_POST['other'] )
			die();

		$title = $_POST['title'];
		$text  = wp_kses_post( $_POST['text'] );
		$email = sanitize_email( $_POST['email'] );

		// If a websubmission user exists, we'll use that user ID.
		$user = get_user_by( 'slug', 'websubmission' );
		if ( is_wp_error( $user ) )
			$user_id = 0;
		else
			$user_id = $user->ID;

		$formatted_dates = array();
		foreach( $_POST['dates'] as $date ) {
			$formatted_dates[] = strtotime( $date );
		}
		sort( $formatted_dates );
		$post_date = date( 'Y-m-d H:i:s', $formatted_dates[0] );

		$post_data = array(
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_author'    => $user_id,
			'post_content'   => $text,    // Sanitized with wp_kses_post(), probably overly so.
			'post_title'     => $title,   // Sanitized in wp_insert_post().
			'post_type'      => 'wsu_policie',
			'post_status'    => 'pending',
			'post_date'      => $post_date,
		);
		$post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			echo 'error';
			exit;
		}

		update_post_meta( $post_id, '_policie_contact_email', $email );

		$this->_save_policie_date_meta( $post_id, $formatted_dates );

		echo 'success';
		exit;
	}

	/**
	 * Modify the WSU Policies post type archive title to properly show date information.
	 *
	 * @param string $name Current title for the archive.
	 *
	 * @return string Modified title for the archive.
	 */
	public function post_type_archive_title( $name ) {

		if ( 'Policies' !== $name )
			return $name;

		// Get the date from our URL because we've tricked the query until now.
		$url_dates = explode( '/', trim( $_SERVER['REQUEST_URI'], '/' ) );
		array_shift( $url_dates );

		if ( isset( $url_dates[2] ) )
			return date( 'F j, Y ', strtotime( $url_dates[0] . '-' . $url_dates[1] . '-' . $url_dates[2] . ' 00:00:00' ) ) . $name;
		elseif ( isset( $url_dates[1] ) && 0 !== absint( $url_dates[0] ) )
			return date( 'F Y ', strtotime( $url_dates[0] . '-' .  $url_dates[1] . '-01 00:00:00' ) ) . $name;
		elseif ( isset( $url_dates[0] ) && 0 !== absint( $url_dates[0] ) )
			return date( 'Y ', strtotime( $url_dates[0] . '-01-01 00:00:00' ) ) . $name;
		else
			return $name;

	}

	/**
	 * Filter the WordPress SEO generate post type archive title for Policies.
	 *
	 * @param string $title Title as previously modified by WordPress SEO
	 *
	 * @return string Our replacement version of the title.
	 */
	public function post_type_archive_wpseo_title( $title ) {
		if ( is_post_type_archive( $this->post_type ) )
			return $this->post_type_archive_title( $this->post_type_name ) . ' |';

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
		// We may use categories and tags, but we don't need them on this screen.
		unset( $columns['categories'] );
		unset( $columns['tags'] );
		unset( $columns['date'] );

		// Remove all WPSEO added columns as we have no use for them on this screen.
		unset( $columns['wpseo-score'] );
		unset( $columns['wpseo-title'] );
		unset( $columns['wpseo-metadesc'] );
		unset( $columns['wpseo-focuskw'] );

		// Add our custom columns. Move date to the end of the array after we unset it above.
		$columns['contact_email'] = 'Contact Email';
		$columns['announce_dates'] = 'Policie Dates';
		$columns['date'] = 'Publish Date';

		return $columns;
	}

	/**
	 * Capture the various days, months, and years on which this policie should appear and
	 * update post meta accordingly so that we can perform custom queries as needed.
	 *
	 * @param int   $post_id         ID of the post to assign the dates to.
	 * @param array $formatted_dates An array of dates the policie will be shown on.
	 */
	private function _save_policie_date_meta( $post_id, $formatted_dates ) {
		foreach( $formatted_dates as $date ) {
			$date_formatted  = date( 'Ymd', $date );
			$month_formatted = date( 'Ym',  $date );
			$year_formatted  = date( 'Y',   $date );

			update_post_meta( $post_id, '_policie_date_'  . $date_formatted,  1 );
			update_post_meta( $post_id, '_policie_date_'  . $month_formatted, 1 );
			update_post_meta( $post_id, '_policie_date_'  . $year_formatted,  1 );
		}
	}

	/**
	 * Retrieve policie date meta for a post.
	 *
	 * @param int $post_id Post ID to retrieve metadata for.
	 *
	 * @return mixed Results of the post meta query.
	 */
	private function _get_policie_date_meta( $post_id ) {
		/* @global WPDB $wpdb */
		global $wpdb;

		$policie_date = '_policie_date_%';
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key FROM $wpdb->postmeta WHERE post_id = %d and meta_key LIKE %s GROUP BY meta_key", $post_id, $policie_date ) );

		return $results;
	}

	/**
	 * Delete any policie dates associated with an policie.
	 *
	 * @param int $post_id Post ID of the policie to clear date data from.
	 */
	private function _clear_policie_date_meta( $post_id ) {
		global $wpdb;

		$policie_key = '_policie_date_%';
		$wpdb->get_results( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s", $post_id, $policie_key ) );
	}

	/**
	 * Handle output for the contact email column in the policie list table.
	 *
	 * @param string $column_name Current column being displayed.
	 * @param int    $post_id     Post ID of the current row being displayed.
	 */
	public function manage_list_table_email_column( $column_name, $post_id ) {
		if ( 'contact_email' !== $column_name )
			return;

		if ( $contact_email = get_post_meta( $post_id, '_policie_contact_email', true ) )
			echo esc_html( $contact_email );
	}

	/**
	 * Handle output for the policie dates column in the policie list table.
	 *
	 * @param string $column_name Current column being displayed.
	 * @param int    $post_id     Post ID of the current row being displayed.
	 */
	public function manage_list_table_policie_dates_column( $column_name, $post_id ) {
		if ( 'announce_dates' !== $column_name )
			return;

		$policie_meta = $this->_get_policie_date_meta( $post_id );

		foreach( $policie_meta as $meta ) {
			$date = str_replace( '_policie_date_', '', $meta->meta_key );

			if ( 8 === strlen( $date ) ) {
				$date_display = substr( $date, 4, 2 ) . '/' . substr( $date, 6, 2 ) . '/' . substr( $date, 0, 4 );
				echo $date_display . '<br>';
			}
		}
	}

	/**
	 * Displays a calendar with links to days that have policies.
	 *
	 * This was originally copied from the WordPress get_calendar() function, but then
	 * heavily modified to query against a post types post meta rather than the
	 * wp_posts table. The HTML structure of the final calendar is vary close, if not
	 * identical to the built in WordPress functionality.
	 *
	 * @param bool $initial Optional, default is true. Use initial calendar names.
	 * @param bool $echo    Optional, default is true. Set to false for return.
	 *
	 * @return null|string  String when retrieving, null when displaying.
	 */
	public function get_calendar( $initial = true, $echo = true ) {
		/**
		 * @global WPDB      $wpdb
		 * @global WP_Locale $wp_locale
		 */
		global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

		$key = md5( $m . $monthnum . $year );

		if ( $cache = get_transient( $this->calendar_cache_key ) ) {
			if ( is_array( $cache ) && isset( $cache[ $key ] ) ) {
				if ( $echo ) {
					echo $cache[ $key ];
					return;
				} else {
					return $cache[ $key ];
				}
			}
		}

		if ( ! is_array( $cache ) )
			$cache = array();

		// Quick check. If we have no posts at all, abort!
		if ( ! $posts ) {
			$gotsome = $wpdb->get_var( $wpdb->prepare( "SELECT 1 as test FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish' LIMIT 1", $this->post_type ) );
			if ( ! $gotsome ) {
				$cache[ $key ] = '';
				set_transient( $this->calendar_cache_key, $cache, 60 * 60 * 24 ); // Will likely be flushed well before then.
				return;
			}
		}

		if ( isset($_GET['w']) )
			$w = '' . intval( $_GET['w'] );

		// week_begins = 0 stands for Sunday
		$week_begins = intval( get_option( 'start_of_week' ) );

		// Let's figure out when we are
		if ( ! empty( $monthnum ) && ! empty( $year ) ) {
			$thismonth = '' . zeroise( intval( $monthnum ), 2 );
			$thisyear  = '' . intval( $year );
		} elseif ( ! empty( $w ) ) {
			// We need to get the month from MySQL
			$thisyear  = '' . intval( substr( $m, 0, 4 ) );
			$d = ( ( $w - 1 ) * 7 ) + 6; //it seems MySQL's weeks disagree with PHP's
			$thismonth = $wpdb->get_var( "SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')" );
		} elseif ( ! empty( $m ) ) {
			$thisyear = '' . intval( substr( $m, 0, 4 ) );
			if ( strlen( $m ) < 6 )
				$thismonth = '01';
			else
				$thismonth = '' . zeroise( intval( substr( $m, 4, 2 ) ), 2 );
		} else {
			$thisyear  = gmdate( 'Y', current_time( 'timestamp' ) );
			$thismonth = gmdate( 'm', current_time( 'timestamp' ) );
		}

		$unixmonth = mktime( 0, 0 , 0, $thismonth, 1, $thisyear );
		$last_day  = date( 't', $unixmonth );

		// @todo Get the next and previous month and year with at least one post
		$previous = false;
		$next     = false;

		$calendar_output = '<table id="wp-calendar">
	<caption><a href="' . esc_url( $this->get_month_link( $thisyear, $thismonth ) ) . '" title="Policies for ' . $thismonth . '/' . $thisyear . '">' . date( 'F Y ') . '</a></caption>
	<thead>
	<tr>';

		$myweek = array();

		for ( $wdcount=0; $wdcount<=6; $wdcount++ ) {
			$myweek[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
		}

		foreach ( $myweek as $wd ) {
			$day_name = ( true == $initial ) ? $wp_locale->get_weekday_initial( $wd ) : $wp_locale->get_weekday_abbrev( $wd );
			$wd = esc_attr( $wd );
			$calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
		}

		$calendar_output .= '
	</tr>
	</thead>

	<tfoot>
	<tr>';

		if ( $previous ) {
			$calendar_output .= "\n\t\t".'<td colspan="3" id="prev"><a href="' . $this->get_month_link( $previous->year, $previous->month ) . '" title="' . esc_attr( sprintf(__('View posts for %1$s %2$s'), $wp_locale->get_month( $previous->month ), date( 'Y', mktime( 0, 0 , 0, $previous->month, 1, $previous->year ) ) ) ) . '">&laquo; ' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $previous->month ) ) . '</a></td>';
		} else {
			$calendar_output .= "\n\t\t".'<td colspan="3" id="prev" class="pad">&nbsp;</td>';
		}

		$calendar_output .= "\n\t\t".'<td class="pad">&nbsp;</td>';

		if ( $next ) {
			$calendar_output .= "\n\t\t".'<td colspan="3" id="next"><a href="' . $this->get_month_link( $next->year, $next->month ) . '" title="' . esc_attr( sprintf(__('View posts for %1$s %2$s'), $wp_locale->get_month( $next->month ), date( 'Y', mktime( 0, 0 , 0, $next->month, 1, $next->year ) ) ) ) . '">' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $next->month ) ) . ' &raquo;</a></td>';
		} else {
			$calendar_output .= "\n\t\t".'<td colspan="3" id="next" class="pad">&nbsp;</td>';
		}

		$calendar_output .= '
	</tr>
	</tfoot>

	<tbody>
	<tr>';

		// Get days with policie data for this month stored in post meta.
		$policie_date_key = '_policie_date_' . $thisyear . $thismonth . '%';
		$days_post_ids = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_key LIKE %s", $policie_date_key ), ARRAY_N );
		$days_post_ids = wp_list_pluck( $days_post_ids, 0 );
		$days_post_ids = join( ',', $days_post_ids );

		// Now that we have a full list of post IDs, we need to make a query for those that are published.
		$days_post_ids = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE ID IN ( " . $days_post_ids . " ) AND post_status ='publish'", ARRAY_N );
		$days_post_ids = wp_list_pluck( $days_post_ids, 0 );
		$days_post_ids = join( ',', $days_post_ids );

		// No go back and get the distinct dates on which these policies are to be made.
		$days_results = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT meta_key FROM $wpdb->postmeta WHERE post_id IN ( " . $days_post_ids . " ) AND meta_key LIKE %s", $policie_date_key ), ARRAY_N );

		$current_day    = date( 'd' ); // We need this to avoid future policies.
		$days_with_post = array();     // Ensure at least an empty array.

		if ( $days_results ) {
			foreach( $days_results as $day_with ) {
				$day_with = str_replace( '_policie_date_' . $thisyear . $thismonth, '', $day_with );
				if ( '' !== $day_with[0] && $current_day >= $day_with[0] )
					$days_with_post[] = $day_with[0];
			}
		}

		// See how much we should pad in the beginning
		$pad = calendar_week_mod( date( 'w', $unixmonth ) - $week_begins );
		if ( 0 != $pad )
			$calendar_output .= "\n\t\t" . '<td colspan="' . esc_attr( $pad ) . '" class="pad">&nbsp;</td>';

		$daysinmonth = intval( date( 't', $unixmonth ) );
		for ( $day = 1; $day <= $daysinmonth; ++$day ) {
			if ( isset( $newrow ) && $newrow )
				$calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
			$newrow = false;

			if ( $day == gmdate( 'j', current_time( 'timestamp' ) ) && $thismonth == gmdate( 'm', current_time( 'timestamp' ) ) && $thisyear == gmdate( 'Y', current_time( 'timestamp' ) ) )
				$calendar_output .= '<td id="today">';
			else
				$calendar_output .= '<td>';

			if ( in_array( $day, $days_with_post ) ) // any posts today?
				$calendar_output .= '<a href="' . $this->get_day_link( $thisyear, $thismonth, $day ) . '" title="' . esc_attr( 'Policies for ' . $thismonth . '/' . $day . '/' . $thisyear ) . " \">$day</a>";
			else
				$calendar_output .= $day;
			$calendar_output .= '</td>';

			if ( 6 == calendar_week_mod( date( 'w', mktime( 0, 0 , 0, $thismonth, $day, $thisyear ) ) - $week_begins ) )
				$newrow = true;
		}

		$pad = 7 - calendar_week_mod( date( 'w', mktime( 0, 0 , 0, $thismonth, $day, $thisyear ) ) - $week_begins );
		if ( $pad != 0 && $pad != 7 )
			$calendar_output .= "\n\t\t" . '<td class="pad" colspan="' . esc_attr( $pad ) . '">&nbsp;</td>';

		$calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

		$cache[ $key ] = $calendar_output;
		set_transient( $this->calendar_cache_key, $cache, 60 * 60 * 24 ); // Will likely be flushed well before this.

		if ( $echo )
			echo $calendar_output;
		else
			return $calendar_output;

		return null;
	}

	/**
	 * Purge cached policie calendar data when an policie is saved or deleted.
	 *
	 * @param int $post_id Current post being acted on.
	 */
	public function delete_calendar_cache( $post_id ) {
		if ( $this->post_type === get_post_type( $post_id ) )
			delete_transient( $this->calendar_cache_key );
	}

	/**
	 * Generate a link to a day's policie archives.
	 *
	 * @param string $year  Year to be included in the URL.
	 * @param string $month Month to be included in the URL.
	 * @param string $day   Day to be included in the URL.
	 *
	 * @return string Day's policie URL.
	 */
	public function get_day_link( $year, $month, $day ) {
		return site_url( $this->post_type_archive . '/' . $year . '/' . $month . '/' . $day . '/' );
	}

	/**
	 * Generate a link to a month's policie archives.
	 *
	 * @param string $year  Year to be included in the URL.
	 * @param string $month Month to be included in the URL.
	 *
	 * @return string Month's policie URL.
	 */
	public function get_month_link( $year, $month ) {
		return site_url( $this->post_type_archive . '/' . $year . '/' . $month . '/' );
	}

	/**
	 * Register widgets used by policies.
	 */
	public function register_widget() {
		register_widget( 'WSU_News_Policie_Calendar_Widget' );
	}
}
$wsu_content_type_policie = new WSU_Content_Type_Policie();

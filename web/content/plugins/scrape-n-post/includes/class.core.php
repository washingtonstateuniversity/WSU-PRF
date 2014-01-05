<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class scrape_core {
	
	public $scrape_pages = NULL;
	public $scrape_output = NULL;
	public $scrape_data = NULL;
	
    public $message = array();
	public $_params;
    function __construct() {
		global $scrape_output,$scrape_data,$_params;
		
		$_params = $_POST; // this needs to get validated and noonced and what not
		if (is_admin()) {
			
			include(SCRAPE_PATH . '/includes/class.pages.php');// Include scrape_pages::
			$scrape_pages = new scrape_pages();

			include(SCRAPE_PATH . '/includes/class.output.php');// Include scrape_output::
			$scrape_output = new scrape_output();

			include(SCRAPE_PATH . '/includes/class.data.php');// Include scrape_data::
			//$scrape_data = new scrape_data();
			
			$options = $scrape_data->get_options(); // after _param validation just in case
			//seems that if xdebug is in use then it'll kill something at 100 when it shouldn't have
			if(isset($options['xdebug_fix']) && $options['xdebug_fix']==1)
				ini_set('xdebug.max_nesting_level', 10000000000000000000000000000000); // should quitely fail if no xdebug
			if(isset($options['time_limit']) && $options['time_limit']>-1)
				set_time_limit($options['time_limit']);
			if(isset($options['memory_limit']) && $options['memory_limit']>-2)
				ini_set('memory_limit', $options['memory_limit']);
        }
    }
    /*
     * Initialize install
     */
    public function install_init() {
        // Add database table
        $this->_add_table();
    }
	
	
	//move installs to install class
    /*
     * Add template table
     */
    public function _add_table() {
        global $wpdb,$scrape_data;
        // Construct query
        $table_name = $wpdb->prefix . "scrape_n_post_queue";
        $sql        = "
		DROP TABLE IF EXISTS `{$table_name}`;
		CREATE TABLE `{$table_name}`  (
			`target_id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			`post_id` MEDIUMINT(9),
			`ignore` VARCHAR(3) DEFAULT NULL,
			`url` TEXT NOT NULL,
			`referrer` TEXT,
			`match_level` TEXT,
			`http_status` MEDIUMINT(9),
			`type` VARCHAR(255) DEFAULT NULL,
			`last_imported` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			`last_checked` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			`added_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	    UNIQUE KEY id (target_id)
		);";
        // Import wordpress database library
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        // Save version
        add_option('scrape_db_version', SCRAPE_VERSION);
        // Add plugin option holder
        $options = $scrape_data->get_options();
        add_option('scrape_options', $options, '', 'yes');
		// Define and create required directories
		$required_dir = array(
			'modules' => SCRAPE_PATH . '/scrape-content/modules',
			'http-cache' => SCRAPE_PATH . '/scrape-content/http-cache'
		);
		foreach ($required_dir as $dir)
			if( !is_dir($dir) ) @mkdir($dir, 0777);
		
		
    }



	//@todo move it on out of here to ?data? class
    /*
     * Check if entry already exist
     * @column - string
     * @value - string
     */
    public function _is_exist($column = '', $value = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . "scrape_n_post_queue";
        $result     = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE " . $column . " = '" . $value . "'");
        return (count($result) > 0);
    }

	
	
	

}
?>
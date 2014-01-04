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
		$_params = $_POST;

		if (is_admin()) {
			// Include output
			include(SCRAPE_PATH . '/includes/class.pages.php');
			$scrape_pages = new scrape_pages();
	
			// Include output
			include(SCRAPE_PATH . '/includes/class.output.php');
			$scrape_output = new scrape_output();
	
			// Include data
			include(SCRAPE_PATH . '/includes/class.data.php');
			$scrape_data = new scrape_data();

        }
       
    }
    /*
     * Initialize install
     */
    public function install_init() {
        // Add database table
        $this->_add_table();
    }
    /*
     * Add template table
     */
    public function _add_table() {
        global $wpdb,$scrape_data;
        // Construct query
        $table_name = $wpdb->prefix . "scrape_n_post_queue";
        $sql        = "
		CREATE TABLE `{$table_name}`  (
			`target_id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`url` text NOT NULL,
			`referrer` text,
			`match_level` text,
			`http_status` text,
			`last_imported` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			`last_checked` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			`added_date` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	    UNIQUE KEY id (template_id)
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

	
    /*
     * Check if entry already exist
     * @column - string
     * @value - string
     */
    private function _is_exist($column = '', $value = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . "scrape_n_post_queue";
        $result     = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE " . $column . " = '" . $value . "'");
        return (count($result) > 0);
    }

}
?>
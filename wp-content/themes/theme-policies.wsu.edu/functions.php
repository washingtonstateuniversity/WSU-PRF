<?php
$gauntlet=null;

function extract_domain($domain){
    if(preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $domain, $matches)){
        return $matches['domain'];
    } else {
        return $domain;
    }
}
function extract_subdomains($domain){
    $subdomains = $domain;
    $domain = extract_domain($subdomains);

    $subdomains = rtrim(strstr($subdomains, $domain, true), '.');

    return $subdomains;
}

/*add_action( 'init', 'wsu_ga_remove_analytics' );
function wsu_ga_remove_analytics() {
	global $wsu_analytics;
	//remove_action( 'wp_enqueue_scripts', array( $wsu_analytics, 'enqueue_scripts' ) );
	//remove_action( 'admin_init', array( $wsu_analytics, 'display_settings' ) );
	//remove_action( 'wp_footer', array( $wsu_analytics, 'global_tracker' ), 999 );
	//remove_action( 'admin_footer', array( $wsu_analytics, 'global_tracker' ), 999 );
}*/



add_action( 'wp_loaded', 'set_gauntlet' );
function set_gauntlet(){
	$site_code = "ga-11";
	$host = $_SERVER['HTTP_HOST'];
	if (strpos($host,'.dev') != true) {
		$site_code = str_replace('.web','',extract_subdomains($host));
	}

	$site_group_code=null;
	$group_ga=null;
	$_ga=null;
	switch($site_code){
		case "ga-1":
		case "ga-2":
		case "ga-3":
			$site_group_code="current";
			$group_ga="UA-55556719-17";
			switch($site_code){
				case "ga-1":
					$_ga="UA-55556719-1";
					break;
				case "ga-2":
					$_ga="UA-55556719-2";
					break;
				case "ga-3":
					$_ga="UA-55556719-3";
					break;
			}
			break;
		case "ga-4":
		case "ga-5":
		case "ga-6":
			$site_group_code="ideal";
			$group_ga="UA-55556719-18";
			switch($site_code){
				case "ga-4":
					$_ga="UA-55556719-4";
					break;
				case "ga-5":
					$_ga="UA-55556719-5";
					break;
				case "ga-6":
					$_ga="UA-55556719-6";
					break;
			}
			break;
		case "ga-7":
		case "ga-8":
		case "ga-9":
			$site_group_code="controll";
			$group_ga="UA-55556719-19";
			switch($site_code){
				case "ga-7":
					$_ga="UA-55556719-7";
					break;
				case "ga-8":
					$_ga="UA-55556719-8";
					break;
				case "ga-9":
					$_ga="UA-55556719-9";
					break;
			}
			break;
		case "ga-10":
		case "ga-11":
		case "ga-12":
			$site_group_code="jtrack";
			$group_ga="UA-55556719-20";
			switch($site_code){
				case "ga-10":
					$_ga="UA-55556719-10";
					break;
				case "ga-11":
					$_ga="UA-55556719-11";
					break;
				case "ga-12":
					$_ga="UA-55556719-12";
					break;
			}
			break;
		case "ga-13":
		case "ga-14":
		case "ga-15":
			$site_group_code="tag_man";
			$group_ga="UA-55556719-21";
			switch($site_code){
				case "ga-13":
					$_ga="UA-55556719-13";
					break;
				case "ga-14":
					$_ga="UA-55556719-14";
					break;
				case "ga-15":
					$_ga="UA-55556719-15";
					break;
			}
			break;
		default:
			//nothing to do
	}
	
	
	//leave it as something exentable
	$GLOBALS['gauntlet'] = array(
		'base_code'		=> $site_code,
		'code'			=> $site_group_code,
		'group_ga'		=> $group_ga,
		'site_ga'		=> $_ga
	);
}

add_action( 'wp_loaded', 'send_test_rules' );
function send_test_rules(){
	$ajax=isset($_GET['ajax']);
	if($ajax){
		$callback=$_GET['callback'];
		$load = get_stylesheet_directory().'/track/'.$_GET['load'];
		
		$data='{}';
		if(file_exists($load.'.txt')){
			$data = file_get_contents($load.'.txt');
		}
		echo $callback . '(' . $data . ')';die();
	}
}



function get_gauntlet_attr($attr=""){
	global $gauntlet;
	return isset( $gauntlet[$attr] ) ? $gauntlet[$attr] : false;
}

/* build the gauntlet nav */
add_filter( 'wp_nav_menu_items', 'gauntlet_nav');
function gauntlet_nav( $items ) {
	$gaut_map= array(
		"current"	=>array("ga-1","ga-2","ga-3"),
		"ideal"		=>array("ga-4","ga-5","ga-6"),
		"controll"	=>array("ga-7","ga-8","ga-9"),
		"jtrack"	=>array("ga-10","ga-11","ga-12"),
		"tag_man"	=>array("ga-13","ga-14","ga-15")
	);
	
	$activeCode = get_gauntlet_attr("base_code");
	$items .= '<li class=""><a href="//ga.wp.wsu.edu/">Google Analytics Testing</a></li>';
	foreach($gaut_map as $key=>$children){
		$child_html="";
		$activeParent = false;
		
		foreach($children as $child){
			if( $activeCode == $child ){
				$items = str_replace('class="current','class="',$items);
				$activeParent = true;
			}
			$child_html .='<li class="'.($activeCode == $child ? 'active' : '' ).'"><a href="http://'.$child.'.web.wsu.edu">'.$child.'</a></li>';
		}
		$items .= '<li class="parent '.($activeParent? 'active' : '' ).'"><a href="#">'.$key.' state</a><ul class="sub-menu">'.$child_html.'</ul></li>';
	}
    return $items;
}




/**
 * set up the site vars in the dom
 */
add_action('wp_head','set_site_code');
function set_site_code() {
	?>

<link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css"/>
<script type="text/javascript">
	var base_code = "<?=get_gauntlet_attr("base_code")?>";
	var site_code = "<?=get_gauntlet_attr("code")?>";
	var themePath = "<?=get_stylesheet_directory_uri()?>";
	$=jQuery;
</script>
<?php }

/**
 * Set up any head blocks if they exist
 */
add_action('wp_head','set_head_block');
function set_head_block() {
	get_template_part('parts/head/'.get_gauntlet_attr("code"));
}

/**
 * Set up any footer blocks if they exist
 */
add_action('wp_footer','set_footer_block');
function set_footer_block() {
	get_template_part('parts/footer/'.get_gauntlet_attr("code"));
}





add_action( 'wp_enqueue_scripts', 'gauntlet_scripts' );
/**
 * Enqueue child theme Javascript files.
 */
function gauntlet_scripts() {
	
	
	
	wp_enqueue_script( 'site.js', get_stylesheet_directory_uri() . '/scripts/site.js', array( 'jquery' ), false, true );
	
}

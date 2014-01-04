<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class scrape_data {
    function __construct() { }

	public function get_options(){
		$plugin_option = get_option('scrape_options',array(
			'sc_posts' => 1,
			'sc_widgets' => 1,
			'on_error' => 'error_hide',
			'custom_error' => 'Unable to fetch data',
			'useragent' => "Scrape-N-Post bot (".get_bloginfo('url').")",
			'timeout' => 2,
			'cache' => 60
        ));	
		return $plugin_option;
	}


	public function build_link_object(){

		return array();
	}

/**
 * Wrapper function to fetch content, select / query it and parse it
 * @param string $url
 * @param string $selector (optional) Selector
 * @param string $xpath (optional) XPath
 * @param array $scrapeopt Options
 * @return string
 */
function scrape_get_content($url, $selector = '', $xpath = '', $scrapeopt = '') {
    $scrape_options = get_option('scrape_options');
    $default_scrapeopt = array(
            'postargs' => '',
            'cache' => $scrape_options['cache'],
            'user_agent' => $scrape_options['useragent'],
            'timeout' => $scrape_options['timeout'],
            'on_error' => $scrape_options['on_error'],
            'output' => 'html',
            'clear_regex' => '',
            'clear_selector' => '',
            'replace_regex' => '',
            'replace_selector' => '',
            'replace_with' => '',
            'replace_selector_with' => '',
            'basehref' => '',
            'striptags' => '',
            'removetags' => '',
            'callback' => '',
            'debug' => '1',
            'htmldecode' => ''
    );
    $scrapeopt = wp_parse_args( $scrapeopt, $default_scrapeopt );
    unset($scrapeopt['url']);
    unset($scrapeopt['selector']);
    unset($scrapeopt['xpath']);

    if($scrapeopt['debug'] == '1') {
        $header = "\n<!--\n Start of web scrape (created by Scrape-N-Post)\n Source URL: $url \n Selector: $selector\n Xpath: $xpath";
        $footer = "\n<!--\n End of web scrape";
    } elseif ($scrapeopt['debug'] == '0') {
        $header = '';
        $footer = '';
    }

    if(empty($url)) {
        $header .= "\n Other options: ".print_r($scrapeopt, true)."-->\n";
        if($scrapeopt['on_error'] == 'error_hide') {
            return $header.$footer;
        } else {
            return "$header Scrape-N-Post Error: No URL and/or selector specified $footer";
        }
    }

    if( strstr($url, '___QUERY_STRING___') ) {
        $url = str_replace('___QUERY_STRING___', $_SERVER['QUERY_STRING'], $url);
    } else {
        $url = preg_replace_callback('/___(.*?)___/', create_function('$matches','return $_REQUEST[$matches[1]];'), $url);
    }

    if( strstr($scrapeopt['postargs'], '___QUERY_STRING___') ) {
        $scrapeopt['postargs'] = str_replace('___QUERY_STRING___', $_SERVER['QUERY_STRING'], $scrapeopt['postargs']);
    } else {
        $scrapeopt['postargs'] = preg_replace_callback('/___(.*?)___/', create_function('$matches','return $_REQUEST[$matches[1]];'), $scrapeopt['postargs']);
    }

    $cache_args['cache'] = $scrapeopt['cache'];

    if ( !empty($scrapeopt['postargs']) ) {
        $http_args['headers'] = $scrapeopt['postargs'];
        $cache_args['headers'] = $scrapeopt['postargs'];
    }
    $http_args['user-agent'] = $scrapeopt['user_agent'];
    $http_args['timeout'] = $scrapeopt['timeout'];

    $response = scrape_remote_request($url, $cache_args, $http_args);
    if( !is_wp_error( $response ) ) {
        $raw_html = $response['body'];
        if( !empty($selector) ) {
            $raw_html = scrape_get_html_by_selector($raw_html, $selector, $scrapeopt['output']);
             if( !is_wp_error( $raw_html ) ) {
                 $filtered_html = $raw_html;
             } else {
                 $err_str = $raw_html->get_error_message();
             }
        } elseif( !empty($xpath) ) {
            $raw_html = scrape_get_html_by_xpath($raw_html, $xpath, $scrapeopt['output']);
             if( !is_wp_error( $raw_html ) ) {
                 $filtered_html = $raw_html;
             } else {
                 $err_str = $raw_html->get_error_message();
             }
        } else {
            $filtered_html = $raw_html;
        }
        if( !empty($err_str) ) {
            if($scrapeopt['debug'] == '1') {
                $header .= "\n Other options: ".print_r($scrapeopt, true)."-->\n";
                $footer .= "\n Computing time: ".round(microtime(true) - $scrapeopt['request_mt'], 4)." seconds \n-->\n";
            }
            if ($scrapeopt['on_error'] == 'error_hide')
                return $header.$footer;
            if ($scrapeopt['on_error'] == 'error_show')
                return $header.$err_str.$footer;
            if ( !empty($scrapeopt['on_error']) )
                return $header.$scrapeopt['on_error'].$footer;
        }
        if($scrapeopt['debug'] == '1') {
            $header .= "\n Delivered thru: ".$response['headers']['source']."\n Scrape-N-Post options: ".print_r($scrapeopt, true)."-->\n";
            //$header .= "\n Scrape-N-Post options: ".print_r($scrapeopt, true)."-->\n";
            $footer .= "\n Computing time: ".round(microtime(true) - $scrapeopt['request_mt'], 4)." seconds \n-->\n";
        }
        return $header.scrape_parse_filtered_html($filtered_html, $scrapeopt).$footer;
    } else {
        if($scrapeopt['debug'] == '1') {
            $header .= "\n Other options: ".print_r($scrapeopt, true)."-->\n";
            $footer .= "\n Computing time: ".round(microtime(true) - $scrapeopt['request_mt'], 4)." seconds \n-->\n";
        }
        if ($scrapeopt['on_error'] == 'error_hide')
            return $header.$footer;
        if ($scrapeopt['on_error'] == 'error_show')
            return $header."Error fetching $url - ".$response->get_error_message().$footer;
        if ( !empty($scrapeopt['on_error']) )
            return $header.$scrapeopt['on_error'].$footer;
    }

}

/**
 * Retrieve the raw response from the HTTP request (or its cached version).
 * Wrapper function to wp_remote_request()
 * @param string $url Site URL to retrieve.
 * @param array $cache_args Optional. Override the defaults.
 * @param array $http_args Optional. Override the defaults.
 * @return WP_Error|array The response or WP_Error on failure.
 */
function scrape_remote_request($url, $cache_args = array(), $http_args = array()) {
    $default_cache_args = array(
        'cache' => 60,
        'on-error' => 'cache'
    );
    $default_http_args = array(
        'user-agent' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)'
    );
    $cache_args = wp_parse_args( $cache_args, $default_cache_args );
    $http_args = wp_parse_args( $http_args, $default_http_args );
    if($cache_args['headers']) {
        $transient = md5($url.serialize($cache_args['headers']));
    } else {
        $transient = md5($url);
    }

    if ( false === ( $cache = get_transient($transient) ) || $cache_args['cache'] == 0 ) {
         $response = wp_remote_request($url, $http_args);
        if( !is_wp_error( $response ) ) {
            if($cache_args['cache'] != 0)
                set_transient($transient, $response, $cache_args['cache'] * 60 );
            @$response['headers']['source'] = 'WP_Http';
            return $response;
        } else {
            return new WP_Error('scrape_remote_request_failed', $response->get_error_message());
        }
    } else {
        $cache = get_transient($transient);
        @$cache['headers']['source'] = 'Cache';
        return $cache;
    }
}

/**
 * Get HTML from a web page using XPath query
 * @param string $raw_html Raw HTML
 * @param string $xpath XPath query
 * @param string $output html or text
 * @return string
 */
function scrape_get_html_by_xpath($raw_html, $xpath, $output = 'html'){
    // Parsing request using JS_Extractor
    require_once 'Extractor/Extractor.php';
    $extractor = new JS_Extractor($raw_html);
    $body = $extractor->query("body")->item(0);
    if (!$result = $body->query($xpath)->item(0)->nodeValue)
        return new WP_Error('scrape_get_html_by_xpath_failed', "Error parsing xpath: $xpath");
    if($output == 'text')
        return strip_tags($result);
    if($output == 'html')
        return $result;
}

/**
 * Get HTML from a web page using selector
 * @param string $raw_html Raw HTML
 * @param string $selector Selector
 * @param string $output html or text
 * @return string
 */
function scrape_get_html_by_selector($raw_html, $selector, $output = 'html'){
    // Parsing request using phpQuery
    $currcharset = get_bloginfo('charset');
    require_once 'phpQuery.php';
    $phpquery = phpQuery::newDocumentHTML($raw_html, $currcharset);
    phpQuery::selectDocument($phpquery);
    if($output == 'text')
        return pq($selector)->text();
    if($output == 'html')
        return pq($selector)->html();
    if( empty($output) )
        return new WP_Error('scrape_get_html_by_selector_failed', "Error parsing selector: $selector");
}

/**
 * Parse filtered content using options
 * @param string $filtered_html Filtered HTML using selector or xpath query
 * @param array $scrapeopt Options array
 * @return string
 */
function scrape_parse_filtered_html($filtered_html, $scrapeopt) {
    $currcharset = get_bloginfo('charset');
    if(!empty($scrapeopt['clear_regex']))
        $filtered_html = preg_replace($scrapeopt['clear_regex'], '', $filtered_html);
    if(!empty($scrapeopt['clear_selector']))
        $filtered_html = str_replace(scrape_get_html_by_selector($filtered_html, $scrapeopt['clear_selector']), '', $filtered_html);
    if(!empty($scrapeopt['replace_regex']))
        $filtered_html = preg_replace($scrapeopt['replace_regex'], $scrapeopt['replace_with'], $filtered_html);
    if(!empty($scrapeopt['replace_selector']))
        $filtered_html = str_replace(scrape_get_html_by_selector($filtered_html, $scrapeopt['replace_selector']), $scrapeopt['replace_selector_with'], $filtered_html);
    if(!empty($scrapeopt['basehref']))
        $filtered_html = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="'.$scrapeopt['basehref'].'$2$3',$filtered_html);
    if(!empty($scrapeopt['striptags']))
        $filtered_html = scrape_strip_only($filtered_html, $scrapeopt['striptags']);
    if(!empty($scrapeopt['removetags']))
        $filtered_html = scrape_strip_only($filtered_html, $scrapeopt['removetags'], true);
    if(!empty($scrapeopt['htmldecode']))
        $filtered_html = iconv($scrapeopt['htmldecode'], $currcharset, $filtered_html);
    if(!empty($scrapeopt['callback']) && function_exists($scrapeopt['callback']))
        $filtered_html = call_user_func($scrapeopt['callback'], $filtered_html);
    return $filtered_html;
}

/**
 * Strip specified tags
 * @param string $str
 * @param string/array $tags
 * @param bool $strip_content
 * @return string
 */
function scrape_strip_only($str, $tags, $strip_content = false) {
    $content = '';
    if(!is_array($tags)) {
        $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
        if(end($tags) == '') array_pop($tags);
    }
    foreach($tags as $tag) {
        if ($strip_content)
             $content = '(.+</'.$tag.'(>|\s[^>]*>)|)';
         $str = preg_replace('#</?'.$tag.'(>|\s[^>]*>)'.$content.'#is', '', $str);
    }
    return $str;
}

/**
 * Degug function
 * @return string
 */
function scrape_debug() {
    $url_content = scrape_get_content('http://google.com/','title','','on_error=error_show&cache=10&timeout=2');
    if ( strpos($url_content,'Error ') !== false ) {
        return 'Fatel error: Scrape-N-Post could not fetch content - may not function properly';
    } else {
        return false;
    }
}






























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
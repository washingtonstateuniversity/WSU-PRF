<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'scrape_actions' ) ) {
	class scrape_actions extends scrape_core {

		function __construct() { }

	
		/*
		 * Insert to template table
		 * @arr - array
		 */
		public function add_queue($arr = array()) {
			global $wpdb;
			$arr['added_date'] = current_time('mysql');
			$table_name         = $wpdb->prefix . "scrape_n_post_queue";
			$rows_affected      = $wpdb->insert($table_name, $arr);
			//needs message
		}
		/*
		 * Update entry in template table
		 * @data - array
		 */
		public function update_queue($arr = array()) {
			global $wpdb,$scrape_core,$_params;
			$where         = array(
				'target_id' => $_params['target_id']
			);
			$arr['last_checked'] = current_time('mysql');
			$table_name    = $wpdb->prefix . "scrape_n_post_queue";
			$rows_affected = $wpdb->update($table_name, $arr, $where);
			//needs message
		}
	/*				$_params['target_id']=$target_id;
					$this->update_queue(array(
						'url'=>$href,
						'type'=>$obj['type'],
						'http_status'=>200
					));
					*/
	
		public function ignore_url($target_id=NULL) {
			global $wpdb,$scrape_core,$_params;
			if( $target_id==NULL && !isset($_params['url']) ){
				 return; // do message
			}else{
				$id = $target_id==NULL ? $_params['url'] : $target_id;
			}
			$where         = array(
				'target_id' => $id
			);
			$arr['ignore'] = 1;
			$table_name    = $wpdb->prefix . "scrape_n_post_queue";
			$rows_affected = $wpdb->update($table_name, $arr, $where);
			//needs message
		}
		public function detach_post($target_id=NULL) {
			global $wpdb,$scrape_core,$_params,$scrape_pages;
			if( $target_id==NULL && !isset($_params['url']) ){
				 return; // do message
			}else{
				$id = $target_id==NULL ? $_params['url'] : $target_id;
			}
			$where         = array(
				'target_id' => $id 
			);
			$arr['post_id'] = NULL;
			$table_name    = $wpdb->prefix . "scrape_n_post_queue";
			$rows_affected = $wpdb->update($table_name, $arr, $where);
			$scrape_pages->foward('scrape-crawler',$scheme='http');
			//needs message
		}
		
		public function url_to_post($post_id=NULL,$target_id=NULL) {
			global $wpdb,$scrape_core,$_params;
			if( $target_id==NULL && !isset($_params['url']) ){
				 return; // do message
			}else{
				$id = $target_id==NULL ? $_params['url'] : $target_id;
			}
			
			if( $post_id==NULL && !isset($_params['post_id']) ){
				 return; // do message
			}else{
				$post_id = $post_id==NULL ? $_params['post_id'] : $post_id;
			}
			
			$where         = array(
				'target_id' => $id 
			);
			$arr['post_id'] = $post_id;
			$arr['last_checked'] = current_time('mysql');
			$table_name    = $wpdb->prefix . "scrape_n_post_queue";
			$rows_affected = $wpdb->update($table_name, $arr, $where);
			$scrape_core->message = array(
					'type' => 'updated',
					'message' => __('Added a new post for the url')
				);
		}
	
	
		public function make_post($target_id=NULL, $arr = array()){
			global $wpdb, $current_user,$scrape_data,$_params;
			
			if( $target_id==NULL && !isset($_params['url']) ){
				 return; // do message
			}else{
				$id = $target_id==NULL ? $_params['url'] : $target_id;
			}
			
			$page = $scrape_data->scrape_get_content($id, 'html');
			if($page=="ERROR::404"){
				var_dump($url); die(); //should be a message no? yes!
			}
			$doc = phpQuery::newDocument($page);
			$title = pq('h2:first')->text();
			$content = pq('body')->remove('h3:first')->remove('p:first')->remove('h2:first')->remove('p:first')->html();
			$catName = pq('p:first')->html();
			//should applie paterens by option
			$catarea = explode('<BR>',$catName);
			$catName = trim($catarea[0]);
	
			
			// Get user info
			$current_user = get_userdata( get_current_user_id());
			$user               = $current_user;
	
			if($user) $author_id=$user->ID; // Outputs 1
			if($author_id<=0)die('user not found');
			
			$cat_ID = 0;
			$catSlug = sanitize_title_with_dashes($catName);
			if ($cat = get_term_by('slug', $catSlug,'category')){
				$cat_ID = $cat->term_id;
			}else{
				wp_insert_term($catName, 'category', array(
					'description' => '',
					'slug' => $catSlug
				));	
				if ($cat = get_term_by('slug', $catSlug,'category')){
					$cat_ID = $cat->term_id;
				}
			}
			
			// Create post object
			$complied = array(
				'post_type' => 'wsu_policy', // yes don't hard code in final   
				'post_title' => $title,
				'post_content' => $content,
				'post_status' => 'draft',
				'comment_status'	=>	'closed',
				'ping_status'		=>	'closed',
				'post_category' => array($cat_ID),
				'post_author' => $author_id,
			);	
			
			$arrs = array_merge($complied,$arr);
			//good so far let make the post
			$post_id = wp_insert_post($arrs);
			//all good let tie the post to the url
			$this->url_to_post($post_id,$id);
		}
	
		public function crawl_from($url=NULL) {
			global $_params,$scrape_core;
			if(isset($_params['url'])){
				$options = get_option( 'scrape_options', array('crawl_depth'=>5) );
				$depth = $options['depth']; 
				$this->traverse_all_urls($_params['url'],$depth);
			}
		}
			
		public function test_crawler(){
			global $scrape_core,$scrape_data,$_params;
			$url = $_params['scrape_url'];
			$res = wp_remote_get($url);
			$page = wp_remote_retrieve_body( $res );
			if(empty($page)){
				$page = $scrape_data->scrape_get_content($url, 'body');
			}
			$doc = phpQuery::newDocument($page);
			$title = pq('title')->text();
			if(empty($title))$title=" error : no title- page didn't render";
			$scrape_core->message = array(
					'type' => 'updated',
					'message' => __('tested '.$url.' and return html &lt;title&gt; '.$title)
				);
		}
		public function findlinks() {
			global $wpdb, $scrape_output,$scrape_data, $_params;
			
			$options = $scrape_data->get_options();
	
			$url=$_params['scrape_url'];
			$scrape_data->rootUrl = parse_url($url, PHP_URL_HOST);
			//var_dump($url);
			$urls = $scrape_data->get_all_urls($url,$options['crawl_depth']);
			//var_dump($urls);
			die("going to build the link array");
	
			$this->download_page();
		}
	
	}	
	global $scrape_actions;
	$scrape_actions = new scrape_actions();
}
?>
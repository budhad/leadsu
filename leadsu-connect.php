<?php
 /* Plugin Name: Офферы leads.su
 * Description: Добавляет тип записи "Офферы" и забивает туда офферы с leads.su
 * Author:      NikiTikiTa
 * Version:     0.0.1
 *
 * Text Domain: leadsu-connect
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * */
$leadsuHelp = new LeadsuHelp();
// Класс управления плагином в админке
require_once plugin_dir_path(__FILE__) . 'inc/lead-admin.php'; 
$admin = new LeadAdmin();

// Класс управления офферами (Скачать, обновить, внести изменения)
require_once plugin_dir_path(__FILE__) . 'inc/lead-offers.php'; 
$offers = new LeadOffers($admin);

// Регистрация типов записей, таксономий, скриптов, стилей
// привязка callback-ов к методам классов
require_once plugin_dir_path(__FILE__) . 'inc/lead-register.php'; 


class LeadsuHelp {
	private $plugin_basename;
	private $plugin_dir_path;
	private $plugin_dir_url;

	function __construct() {
		$this->plugin_basename = plugin_basename(__FILE__);
		$this->plugin_dir_path = plugin_dir_path(__FILE__);
		$this->plugin_dir_url = plugin_dir_url(__FILE__);
	}

	public function get_plugin_basename() {
		return $this->plugin_basename;
	}
	public function get_plugin_dir_path() {
		return $this->plugin_dir_path;
	}
	public function get_plugin_dir_url() {
		return $this->plugin_dir_url;
	}
}

// перенсти в function.php 
// кастомизация состоянных ссылок

// function lead_offers_remove_slug( $post_link, $post, $leavename ) {
//     if ( 'lead_offers' != $post->post_type || 'publish' != $post->post_status ) {
//         return $post_link;
//     }
//     $post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link );
//     return $post_link;
// }
// add_filter( 'post_type_link', 'lead_offers_remove_slug', 10, 3 );

// function lead_offers_parse_request( $query ) {
//     if ( ! $query->is_main_query() || 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
//         return;
//     }
//     if ( ! empty( $query->query['name'] ) ) {
//         $query->set( 'post_type', array( 'post', 'lead_offers', 'page' ) );
//     }
// }
// add_action( 'pre_get_posts', 'lead_offers_parse_request' );



// add_filter('request', 'rudr_change_term_request', 1, 1 );
// function rudr_change_term_request($query){

// 	$tax_name = 'lead_categories'; // specify you taxonomy name here, it can be also 'category' or 'post_tag'

// 	// Request for child terms differs, we should make an additional check
// 	if( isset($query['attachment']) && $query['attachment'] ) :
// 		$include_children = true;
// 		$name = $query['attachment'];
// 	else:
// 		$include_children = false;
// 		$name = $query['name'];
// 	endif;


// 	$term = get_term_by('slug', $name, $tax_name); // get the current term to make sure it exists

// 	if (isset($name) && $term && !is_wp_error($term)): // check it here
// 		if( $include_children ) {
// 			unset($query['attachment']);
// 			$parent = $term->parent;
// 			while( $parent ) {
// 				$parent_term = get_term( $parent, $tax_name);
// 				$name = $parent_term->slug . '/' . $name;
// 				$parent = $parent_term->parent;
// 			}
// 		} else {
// 			unset($query['name']);
// 		}

// 		switch( $tax_name ):
// 			case 'category':
// 				$query['category_name'] = $name; // for categories
// 			break;
		
// 			case 'post_tag':
// 				$query['tag'] = $name; // for post tags
// 			break;
		
// 			default:
// 				$query[$tax_name] = $name; // for another taxonomies
// 			break;

// 		endswitch;
// 	endif;

// 	return $query;
// }

// add_filter( 'term_link', 'rudr_term_permalink', 10, 3 );
// function rudr_term_permalink( $url, $term, $taxonomy ){

// 	$taxonomy_name = 'lead_categories'; // your taxonomy name here
// 	$taxonomy_slug = 'lead_categories'; // the taxonomy slug can be different with the taxonomy name (like 'post_tag' and 'tag' )

// 	// exit the function if taxonomy slug is not in URL
// 	if ( strpos($url, $taxonomy_slug) === FALSE || $taxonomy != $taxonomy_name ) {
// 		return $url;
// 	}

// 	$url = str_replace('/' . $taxonomy_slug, '', $url);

// 	return $url;
// }

// если страница карты сайта, и есть GEt параметр таксономии - добавим к title имя таксономии
// function map_title_with_get_aprams( $title ) { 
// 	$change_title = $title;
// 	if (is_page('map')) {
// 		if (isset($_GET['section'])) {
// 			$term = get_term( $_GET['section'] );
// 			if (!is_wp_error($term)) {
// 				$change_title .= ' » ' . $term->name;
// 			}
// 		} else {
			
// 		}
// 	} 
// 	return $change_title;
// }; 
// add_filter( 'wpseo_title', 'map_title_with_get_aprams'); 
?>
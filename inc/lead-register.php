<?php


/* 
*   lead-admin.php 
*/

add_action( 'admin_notices', [$admin, 'show_help_area'] );

add_action('admin_init', [$admin, 'lead_admin_settings']);

add_action('admin_menu', [$admin, 'add_admin_menu_page']);

// регистрация типов постов и таксономий
add_action('init', [$admin, 'register_post_and_tax']);
// добавляет колонку к офферам в админке
add_filter('manage_lead_offers_posts_columns', [$admin, 'add_offer_action_column']);
// заполняет дынными колонку офферов в админке
add_filter('manage_lead_offers_posts_custom_column', [$admin, 'fill_offer_action_column'], 5, 2);
// Добавим ссылку на страницу настроек в таблицу плагинов
add_filter("plugin_action_links_" . $leadsuHelp->get_plugin_basename() , [$admin, 'lead_plugin_settings_link'] );


/* 
*   lead-offers.php 
*/

// Добавялет мета блок для офферов
add_action('add_meta_boxes', [$offers, 'add_meta_box_for_offers'], 1);
// сохраняет мета поля
add_action( 'save_post', [$offers, 'save_meta_box_for_offers'] );


// Регситрация обработчика fetch запроса на получение списка платформ
// add_action('wp_ajax_get_list_platform', [$offers, 'get_list_platform']);
// add_action('wp_ajax_nopriv_get_list_platform', [$offers, 'get_list_platform']);

// Регситрация обработчика fetch запроса на создание категорий
add_action('wp_ajax_recreate_cats', [$offers, 'recreate_cats']);
add_action('wp_ajax_nopriv_recreate_cats', [$offers, 'recreate_cats']);
// Регситрация обработчика fetch запроса на загрузкувсех офферов площадки
add_action('wp_ajax_download_offers', [$offers, 'download_offers']);
add_action('wp_ajax_nopriv_download_offers', [$offers, 'download_offers']);
// Регситрация обработчика fetch запроса на создание оффера
add_action('wp_ajax_create_offer', [$offers, 'create_offer']);
add_action('wp_ajax_nopriv_create_offer', [$offers, 'create_offer']);
// Регситрация обработчика fetch запроса на переключение состояния оффера
add_action('wp_ajax_toggle_state_offer', [$offers, 'toggle_state_offer']);
add_action('wp_ajax_nopriv_toggle_state_offer', [$offers, 'toggle_state_offer']);

/*
*
*/

add_action('admin_enqueue_scripts', 'lead_enqueue_styles');
function lead_enqueue_styles( $hook_suffix ) {
    global $leadsuHelp;
    wp_enqueue_style( 'lead_admin', $leadsuHelp->get_plugin_dir_url() . 'assets/css/lead_admin.css', array() );
}

add_action('admin_enqueue_scripts', 'lead_enqueue_scripts');
function lead_enqueue_scripts( $hook_suffix ) {
    global $leadsuHelp;
    wp_enqueue_script( 'lead_admin', $leadsuHelp->get_plugin_dir_url() . 'assets/js/lead_admin.js', array('jquery') );
}
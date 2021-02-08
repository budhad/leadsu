<?php

class LeadAdmin {
    // private $offers;
    private $options = [];
    // private $offer_meta_boxes = [
    //     'id_lead'   => 'id',
    //     'offer_url'   => 'offer_url'
    // ];

    function __construct() {
        $this->options = get_option('lead_options');
    }

    // Регистрация страницы настроек, опций плагина
    public function lead_admin_settings() {
        // параметры: $option_group, $option_name, $sanitize_callback
        register_setting( 'lead_option_group', 'lead_options', [$this, 'lead_validation_settings'] );

        // параметры: $id, $title, $callback, $page
        add_settings_section( 'lead_sections', 'Основные настройки', '', 'lead_settings' ); 

        // параметры: $id, $title, $callback, $page, $section, $args
        $field_token = array(
            // 'type'      => 'text', 
            'id'        => 'lead_token',
            // 'desc'      => 'Токен с сайта Leads.su..', // описание
            'label_for' => 'lead_token' 
        );
        add_settings_field( 'field_token', 'Токен с сайта Leads.su', [$this, 'lead_options_display'], 'lead_settings', 'lead_sections', $field_token );

        // параметры: $id, $title, $callback, $page, $section, $args
        $field_platform_id = array(
            // 'type'      => 'text', 
            'id'        => 'platform_id',
            'label_for' => 'platform_id' 
        );
        add_settings_field( 'field_platform_id', 'ID площадки с сайта Leads.su', [$this, 'lead_options_display'], 'lead_settings', 'lead_sections', $field_platform_id );

        // параметры: $id, $title, $callback, $page, $section, $args
        $order_offers_by = array(
            'id'        => 'order_offers_by',
            'label_for' => 'order_offers_by' 
        );
        add_settings_field( 'order_offers_by', 'Сортировать офферы по: ', [$this, 'lead_options_display'], 'lead_settings', 'lead_sections', $order_offers_by );

        // параметры: $id, $title, $callback, $page, $section, $args
        $time_synchronization = array(
            'type'      => 'number',
            'id'        => 'time_synchronization',
            'label_for' => 'time_synchronization',
            'min'       => 1,
            'step'      => 1,
            'value'     => 12
        );
        add_settings_field( 'time_synchronization', 'Период синхронизации офферов (в часах): ', [$this, 'lead_options_display'], 'lead_settings', 'lead_sections', $time_synchronization );
    }

    // callback Для всех input формы настроек
    public function lead_options_display($args) {
        // update_option('ljusers_userinfo', $userinfo_url);
        extract( $args );
        $val = '';
        if (!empty($this->options[$id])) {
            $val = esc_attr( stripslashes($this->options[$id]) );
        } 

        switch ( $id ) {
            case 'lead_token': 
            case 'platform_id':
                echo "<input class='regular-text' type='text' id='$id' name='lead_options[$id]' value='$val' />";  
                echo "<br />"; 
            break;
            case 'order_offers_by':
                echo "<select id='$id' name='lead_options[$id]'>";
                    if (!$val) {
                        echo "<option disabled selected>Выберите поле для сортировки</option>";
                    }
                    $selected = '';
                    $selected = $val == 'system_other_cr' ? 'selected' : '';
                    echo "<option $selected value='system_other_cr'>CR</option>";
                    $selected = '';
                    $selected = $val == 'system_other_ar' ? 'selected' : '';
                    echo "<option $selected value='system_other_ar'>AR</option>";
                    $selected = '';
                    $selected = $val == 'system_other_epc' ? 'selected' : '';
                    echo "<option $selected value='system_other_epc'>EPC</option>";
                    $selected = '';
                    $selected = $val == 'system_other_epl' ? 'selected' : '';
                    echo "<option $selected value='system_other_epl'>EPL</option>";
                    $selected = '';
                    $selected = $val == 'system_other_ctr' ? 'selected' : '';
                    echo "<option $selected value='system_other_ctr'>CTR</option>";
                echo "</select>";  
                echo "<br />"; 
            break;
            case 'time_synchronization':
                $val = $val ? $val : $value;
                echo "<input type='$type' min='$min' step='$step' name='lead_options[$id]' value='$val'>";
            break;
        }
    }
    
    // Валидация введнных данных
    public function lead_validation_settings($input) {
        foreach($input as $k => $v) {
            $valid_input[$k] = trim($v);
            /* проверки значений */
        }
        return $valid_input;
    }

    // Получить значение настройки плагина, если она есть
    public function get_option($name) {
        if ($this->options){
            return $this->options[$name] ?: '-1'; 
        }
    }

    // html страницы нсатроек
    public function get_settings_page() { ?>
        <div class="wrap">
            
            <form id="lead_options_form" action="options.php" method="POST">
                <?php 
                    settings_fields( 'lead_option_group' );   
                    do_settings_sections( 'lead_settings' ); 
                    submit_button('Сохранить настройки');
                ?>
                <?= $this->show_available_events() ?>
            </form>

            
        </div>
    <?php
    }

    // Показывает сообщения о необходимости заполнить поля
    // классы сообщений:
    // notice-success - для успешных операций. Зеленая полоска слева.
    // notice-error - для ошибок. Красная полоска слева.
    // notice-warning - для предупреждений. Оранжевая полоска слева.
    // notice-info - для информации. Синяя полоска слева.
    // is-dismissible - добавляет иконку-кнопку "закрыть" (крестик в конце блока). Иконка добавляется через javascript. По клику на нее блок-заметка будет скрыт (удален), но это состояние не сохраняется, то есть при обновлении страницы блок снова будет отображаться.
    public function show_help_area() {
        $current_screen = get_current_screen();
        if ($current_screen->base == 'settings_page_lead_settings') {
            $count = 0;
            if ( $this->get_option('lead_token') == -1) {            
                $count++;
                echo "<div class='notice notice-warning is-dismissible'>";
                echo "<p>{$count} - Заполните поле Токен для начала работы с плагином</p>";
                echo "</div>";
            };
            
            if ( $this->get_option('platform_id') == -1) {
                $count++;
                echo "<div class='notice notice-warning is-dismissible'>";
                echo "<p>{$count} - Для получения офферов с Leads.su, заполните поле 'ID площадки'.</p>";
                echo "</div>";
            }
        } else {
            if ( $this->get_option('lead_token') == -1 && $this->get_option('platform_id') == -1) { 
                echo "<div class='notice notice-warning is-dismissible'>";
                echo "<p>Для начала работы с плагином Leads.su перейдите к <a href='options-general.php?page=lead_settings'>настройкам</a></p>";
                echo "</div>";
            }
        }
    }

    // отобразить возможные действия с плагином
    private function show_available_events() {
        $result = '';
        if ($this->get_option('lead_token') != -1) {
            $result .= '<p class="lead_settings_btn_line"><button class="button" id="create_offer_cats">Скачать категории офферов</button></p>';
        
            if ($this->get_option('platform_id') != -1) {
                $result .= '<p class="lead_settings_btn_line"><button class="button" id="create_offers">Скачать все офферы для площадки</button></p>';
            }; 
        };     
        $this->update_state_old_post();
        
        return $result . '<hr>';        

    }
    
    // обновлене мета поля offer_state у офферов на старых площадках
    public function update_state_old_post() {
        $old_offers = [
            'post_type'         => 'lead_offers',
            'posts_per_page'    => -1,
            'meta_query'        => array(
                'relation'      => 'AND',
                'offer_state'   => array(
                    'key'   => 'offer_state',
                    'compare' => 'NOT EXISTS'
                )
            )
        ];
        $old_offers = new WP_Query($old_offers);
        if ($old_offers->have_posts()) {
            while ($old_offers->have_posts()) {
                $old_offers->the_post();
                update_post_meta( $old_offers->post->ID, 'offer_state', 'active' );
            }
            wp_reset_postdata(  );
        }
    }
    // 
    // 
    // 

    // Добавим ссылку на страницу настроек в таблицу плагинов
    public function lead_plugin_settings_link($links) { 
        $settings_link = "<a href='options-general.php?page=lead_settings'>Настройки</a>"; 
        array_unshift( $links, $settings_link ); 
        return $links; 
    }

    // регистрация типов постов и таксономий
    public function register_post_and_tax() {
        register_post_type( 'lead_offers', array(
            'label' => 'Офферы',
            'labels'    => array(
                'name'               => 'Офферы',
                'singular_name'      => 'Оффер',
                'add_new'            => 'Добавить новый',
                'add_new_item'       => 'Добавить новый оффер',
                'edit_item'          => 'Редактировать оффер',
                'new_item'           => 'Новый оффер',
                'view_item'          => 'Посмотреть оффер',
                'search_items'       => 'Найти оффер',
                'not_found'          => 'Офферов не найдено',
                'not_found_in_trash' => 'В корзине оффероы не найдено',
                'parent_item_colon'  => '',
                'menu_name'          => 'Офферы'
            ),
            'public'    => true,
            'has_archive'    => true,
            'menu_icon' => 'dashicons-plugins-checked',
            'supports'  => ['thumbnail', 'title', 'editor', 'custom-fields', 'comments'],
            'rewrite'   => array(
                'slug'  => 'offers1'
            )
        ) );
    
        register_taxonomy('lead_categories', ['lead_offers'] ,array(
            'label' => 'Категории офферов',
            'labels'    => array(
                'name'               => 'Категории офферов',
                'singular_name'      => 'Категория оффера',
                'add_new'            => 'Добавить категорию оффера',
                'add_new_item'       => 'Добавить категорию оффера',
                'edit_item'          => 'Редактировать категорию оффера',
                'new_item'           => 'Новая категория оффера',
                'view_item'          => 'Посмотреть категорию оффера',
                'search_items'       => 'Найти категорию оффера',
                'not_found'          => 'Категорий офферов не найдено',
                'parent_item_colon'  => '',
                'menu_name'          => 'Категории офферов'
            ),
            'public'    => true,
            'hierarchical'  => true,
            'show_admin_column' => true,
            'show_in_quick_edit'    => true,
            'rewrite'   => array(
                'slug'  => 'catoff1'
            )
        ));
        // register_taxonomy('lead_bests_categories', ['lead_offers'] ,array(
        //     'label' => 'Категории лучших предложений',
        //     'labels'    => array(
        //         'name'               => 'Категории лучших предложений',
        //         'singular_name'      => 'Категория лучшего предложения',
        //         'add_new'            => 'Добавить категорию лучшего предложения',
        //         'add_new_item'       => 'Добавить категорию лучшего предложения',
        //         'edit_item'          => 'Редактировать категорию лучшего предложения',
        //         'new_item'           => 'Новая категория лучшего предложения',
        //         'view_item'          => 'Посмотреть категорию лучшего предложения',
        //         'search_items'       => 'Найти категорию лучшего предложения',
        //         'not_found'          => 'Категорий лучших предложений не найдено',
        //         'parent_item_colon'  => '',
        //         'menu_name'          => 'Категории лучших предложений'
        //     ),
        //     'public'    => true,
        //     'hierarchical'  => true,
        //     'show_admin_column' => true,
        //     'show_in_quick_edit'    => true,
        // ));
    }

    public function add_admin_menu_page() {
        add_submenu_page( 'options-general.php', 'Настройки офферов', 'Настройки офферов', 'manage_options', 'lead_settings', [$this, 'get_settings_page'], 10 ); 
    }

    // добвить колонку в админке у офферов
    public function add_offer_action_column($columns) {
        $enum = 6; // позиция колонки
        $new_columns = array(
            'offer_state' => 'Состояние',
        );
        return array_slice( $columns, 0, $enum ) + $new_columns + array_slice($columns, $enum);
    }
    // заолнить колонку в админке у офферов
    function fill_offer_action_column($colname, $offer_id) {
        // var_dump($offer_id);
        if ($colname == 'offer_state') {
            $offer_state = get_post_meta($offer_id, 'offer_state', 1);
            switch ($offer_state) {
                case 'acive':
                    echo "<span class='$offer_state'>Активен</span><br/>";
                    echo "<button class='button toggle_state_offer active_offer' data-offer_id='$offer_id'>Отключить</button>";
                break;
                case 'disable':
                    echo "<span class='$offer_state'>Отключен</span><br/>";
                    echo "<button class='button toggle_state_offer disable_offer' data-offer_id='$offer_id'>Включить</button>";
                break;
                default: 
                    echo "<span class='active'>Активен</span><br/>";
                    echo "<button class='button toggle_state_offer active_offer' data-offer_id='$offer_id'>Отключить</button>";
                break;
            }
        }
    }

}


?>
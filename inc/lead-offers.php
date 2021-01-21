<?php 

class LeadOffers {
    private $protocol;
    private $admin = null;
    // 
    private $platforms = [];
    // 
    private $local_cats = [];
    private $local_cats_id = [];
    private $loading_cats = [];
    private $loading_cats_id = [];
    // 
    private $loading_offers = [];
    private $loading_offers_id = [];
    private $local_offers = [];
    private $local_offers_id = [];

    function __construct($admin) {
        $this->protocol = isset($SERVER['HTTPS']) ? 'https:' : 'http:';
        $this->admin = $admin;
    }

    // добавить мета поля 
    public function add_meta_box_for_offers() {
        add_meta_box( 'offer_meta_box', 'Дополнительные параметры оффера', [$this, 'offer_meta_box_callback'], 'lead_offers', 'normal', 'high');
    }

    public function offer_meta_box_callback( $offer ) {
        $id_lead = get_post_meta($offer->ID, 'id_lead', 1); 
        $offer_url = get_post_meta($offer->ID, 'offer_url', 1); 
        $summa_after = get_post_meta($offer->ID, 'summa_after', 1); 
        $summa_before = get_post_meta($offer->ID, 'summa_before', 1); 
        $proc = get_post_meta($offer->ID, 'proc', 1); 
        $kred = get_post_meta($offer->ID, 'kred', 1); 
        $age = get_post_meta($offer->ID, 'age', 1); 
        $time_request = get_post_meta($offer->ID, 'time_request', 1); 
        $system_other_ctr = get_post_meta($offer->ID, 'system_other_ctr', 1); 
        $system_other_cr = get_post_meta($offer->ID, 'system_other_cr', 1); 
        $system_other_ar = get_post_meta($offer->ID, 'system_other_ar', 1); 
        $system_other_epc = get_post_meta($offer->ID, 'system_other_epc', 1); 
        $system_other_epl = get_post_meta($offer->ID, 'system_other_epl', 1); 
        
        ?>
            <table class="lead_meta_box_table">
                <tbody>
                    <tr>
                        <th><label for="offer_url" class="lead_meta_label">Партнерская ссылка: </label></th>
                        <td><input id="offer_url" class="lead_meta_input" type="text" name="offer_url" value="<?= $offer_url ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="summa_before" class="lead_meta_label">Сумма "От": </label></th>
                        <td><input id="summa_before" class="lead_meta_input" type="text" name="summa_before" value="<?= $summa_before ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="summa_after" class="lead_meta_label">Сумма "До": </label></th>
                        <td><input id="summa_after" class="lead_meta_input" type="text" name="summa_after" value="<?= $summa_after ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="proc" class="lead_meta_label">Процент: </label></th>
                        <td><input id="proc" class="lead_meta_input" type="text" name="proc" value="<?= $proc ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="kred" class="lead_meta_label">Срок кредитования: </label></th>
                        <td><input id="kred" class="lead_meta_input" type="text" name="kred" value="<?= $kred ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="age" class="lead_meta_label">Возраст заемщика: </label></th>
                        <td><input id="age" class="lead_meta_input" type="text" name="age" value="<?= $age ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="time_request" class="lead_meta_label">Время рассмотрения: </label></th>
                        <td><input id="time_request" class="lead_meta_input" type="text" name="time_request" value="<?= $time_request ?>"></td>
                    </tr>
                    <tr>
                        <th collspan="2">    
                        <hr>
                            <ul>
                                <li>CR: <?= $system_other_cr ?>%</li>
                                <li>AR: <?= $system_other_ar ?>%</li>
                                <li>EPC: <?= $system_other_epc ?></li>
                                <li>EPL: <?= $system_other_epl ?></li>
                                <li>CTR: <?= $system_other_ctr ?></li>
                            </ul>
                        </th>
                    </tr>
                </tbody>
            </table>
            <input type="hidden" name="offer_meta_fields_nonce" value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>" />
        <?php
    }

    public function save_meta_box_for_offers($offer_id){
        // проверяем nonce нашей страницы, потому что save_post может быть вызван с другого места.
        if ( ! isset($_POST['offer_meta_fields_nonce']) )
            return;
        if ( ! wp_verify_nonce( $_POST['offer_meta_fields_nonce'], plugin_basename(__FILE__)) )
            return;

        // если это автосохранение ничего не делаем
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
            return;

        // проверяем права юзера
        if( ! current_user_can( 'edit_post', $offer_id ) )
            return;

        $offer_url = sanitize_text_field( $_POST['offer_url'] );
        $summa_after = sanitize_text_field( $_POST['summa_after'] );
        $summa_before = sanitize_text_field( $_POST['summa_before'] );
        $proc = sanitize_text_field( $_POST['proc'] );
        $kred = sanitize_text_field( $_POST['kred'] );
        $age = sanitize_text_field( $_POST['age'] );

        // Обновляем данные в базе данных.
        update_post_meta( $offer_id, 'offer_url', $offer_url );
        update_post_meta( $offer_id, 'summa_after', $summa_after );
        update_post_meta( $offer_id, 'summa_before', $summa_before );
        update_post_meta( $offer_id, 'proc', $proc );
        update_post_meta( $offer_id, 'kred', $kred );
        update_post_meta( $offer_id, 'age', $age );
    }

    /* 
    *   cats
    */

    public function recreate_cats($name = '') {
        $result = [
            'message'       => '',
            'result'        => true,
            'new_cats_id'   => []
        ];

        $this->loading_cats = [];
        $this->loading_cats_id = [];
        
        $this->download_all_cats(); 
        
        foreach ($this->loading_cats as $loading_cat){
            $this->loading_cats_id[] = $loading_cat['id'];
        }

        $this->get_local_cats($this->loading_cats_id);
        
        $for_create_cats_id = array_diff( $this->loading_cats_id, $this->local_cats_id );

        foreach ($this->loading_cats as $for_create_cat){
            if (!in_array($for_create_cat['id'], $for_create_cats_id)) continue;

            $result_ins = wp_insert_term( $for_create_cat['name'], 'lead_categories', array() );
            if( ! is_wp_error($result_ins) ) {
                $result['new_cats_id'][] = $result_ins['term_id'];
                $term_id = $result_ins['term_id'];
                add_term_meta( $term_id, 'id_lead_cat', $for_create_cat['id'], true );
            } else {
                $result['message'] = $result_ins->get_error_message();
                $result['result'] = false;
                $result['id'] = $for_create_cat['id'];
                $result['for_create_cats_id'] = $for_create_cats_id;
                $result['loading_cats_id'] = $this->loading_cats_id;
                $result['local_cats_id'] = $this->local_cats_id;
                $result['local_cats'] = $this->local_cats;
            }
        }

        echo json_encode($result);
        exit; 
    }

    private function download_all_cats($limit = 50, $offset = 0) {
        $count_loading_cats = 0;

        $answer = file_get_contents("{$this->protocol}//api.leads.su/webmaster/dictionary/categories?limit={$limit}&offset={$offset}&token={$this->admin->get_option('lead_token')}");
        $answer = json_decode($answer, true);

        $this->loading_cats = array_merge($this->loading_cats, $answer['data']);

        if ($limit == count($answer['data'])){
            $offset += 60;            
            $answer = $this->download_all_cats($limit, $offset);        
        }

        return $answer;
    }

    public function get_local_cats($loading_cats_id = []) {
       
        $atts = [
            'taxonomy'  => 'lead_categories',
            'type'      => 'lead_offers',
            'hide_empty'=> false,
            'meta_query'    => [
                'relation'  => 'OR',
                'id_lead_cat'   => [
                    'key'   => 'id_lead_cat',
                    'type'  => 'NUMERIC',
                    'meta_value_num' => $loading_cats_id,
                    'compare'   => 'IN'
                ]
            ]

        ];
        $this->local_cats = get_categories( $atts );
        $this->local_cats_id = [];
        foreach ($this->local_cats as $local_cat) {
            $this->local_cats_id[] = get_term_meta($local_cat->cat_ID, 'id_lead_cat', true);
        };
        return $this->local_cats;
    }


    /* 
    *   offers
    */

    private function download_with_limit($limit, $offset) {
        $answer = file_get_contents("{$this->protocol}//api.leads.su/webmaster/offers/connectedPlatforms?offset=$offset&limit=$limit&platform_id={$this->admin->get_option('platform_id')}&token={$this->admin->get_option('lead_token')}");
        $answer = json_decode($answer, true);
        return $answer;
    }

    public function download_offers() {
        $result = [
            'message'   => 'Офферы получены успешно',
            'err'       => '',
            'result'    => true,
            'offers'    => []
        ];
        $limit = 50;
        $offset = 0;
        
        do {
            $answer = $this->download_with_limit($limit, $offset);
            if ($answer['code'] != 200) {
                $result['message'] = 'Ошибка получения офферов';
                $result['err'] = $answer['code'];
                $result['result'] = false;
            } else {
                $offset += $limit;
                $result['offers'] = array_merge($result['offers'], $answer['data']);
            }
        } while (count($answer['data']) == $limit);
        
        if (isset($_POST['action'])) {
            echo json_encode($result);
            exit;;
        }
    }

    public function create_offer(){
        $result = [
            'message'   => '',
            'err'       => '',
            'result'    => true,
        ];
        if (!isset($_POST['offer'])) {
            $result['message'] = 'Недостаточно данных для обновления';
            $result['result'] = false;
            echo json_encode($result);
            return;
        };     
        $offer_for_create = json_decode( wp_unslash( $_POST['offer'] ));   
        
        $atts = [
            'numberposts'   => 1,
            'post_type'     => 'lead_offers',
            'meta_query'    => array(
                'id_lead'   => array(
                    'key'   => 'id_lead',
                    'type'  => 'NUMERIC',
                    'value' => $offer_for_create->id,
                    'compare'   => '='
                )
            )
        ];
        $local_offer = new WP_Query( $atts );

        $result['offer_for_createID'] = $offer_for_create->id;
        $result['local_offer'] = $local_offer;

        $offer_name = $this->cut_offer_name($offer_for_create->name);

        if ($local_offer->have_posts()) {
            $result['message'] = "{$offer_name} -- оффер  уже существует";   
            $result['result'] = false;
        } else {
            $result_ins = $this->insert_offer($offer_for_create);
            if (is_wp_error($result_ins)) {
                $result['message'] = "{$offer_name} -- ошибка создания оффера";
                $result['err'] = $result_ins->get_error_message();
                $result['result'] = false;
            } else {
                $this->set_img_offer( $result_ins, $offer_for_create->logo );
                // получаем локальные id категорий по id с leads.su
                $id_local_cats = $this->get_conform_cats_id($offer_for_create->categories);
                $result_cat = wp_set_object_terms( $result_ins, $id_local_cats, 'lead_categories', true );
                $result['message'] = "{$offer_name} -- оффер добавлен";
            }
        }
        if (isset($_POST['action'])) {
            echo json_encode($result);
            exit;
        }
    }

    public function insert_offer($offer) {
        $details_stats_system = $offer->detail_stats->system;

        $offer_name = $this->cut_offer_name($offer->name);
        
        $offer_data = [
            'post_title'    => $offer_name,
            'post_content'  => $offer->description,
            'post_type'     => 'lead_offers',
            'post_status'   => 'publish',
            'meta_input'    => [
                'id_lead'   => $offer->id,
                'offer_url' => $offer->offer_url,
                'summa_after'       => 50000,
                'summa_before'      => 0,
                'proc'              => 0,
                'kred'              => 6,
                'age'               => 18,
                'offer_state'      => 'active',
                'time_request'      => 'Моментально',
                'system_other_ctr' => $details_stats_system->other_ctr,
                'system_other_cr'  => $details_stats_system->other_cr,
                'system_other_ar'  => $details_stats_system->other_ar,
                'system_other_epc' => $details_stats_system->other_epc,
                'system_other_epl' => $details_stats_system->other_epl,
                
            ]
        ];
        $result_ins = wp_insert_post( wp_slash( $offer_data ));
        return $result_ins;
    }

    // обрезать имя оффера до квадратной
    private function cut_offer_name($name) {
        $offer_name = $name;
        $for_cut = stripos($offer_name,'[');
        if ($for_cut) {
            $offer_name = substr($offer_name, 0, $for_cut);
        }
        return $offer_name;
    }

    // needs fuction

    private function get_conform_cats_id($cats_id) {
        $result = [];

        $atts = [
            'taxonomy'  => 'lead_categories',
            'type'      => 'lead_offers',
            'hide_empty'=> false,
            'meta_query'    => [
                'relation'  => 'OR',
                'id_lead_cat'   => [
                    'key'       => 'id_lead_cat',
                    'value'     => $cats_id,
                    'compare'   => 'IN'
                ]
            ]

        ];
        $cats = get_categories( $atts );  

        foreach ($cats as $cat) {
            $result[] = $cat->cat_ID ;
        }

        return $result;
    }

    // скачивает и устанавливает картинку поста
    private function set_img_offer($offer_id, $url_logo) {
        // установим картинку поста
        $img = file_get_contents($url_logo);

        $path = wp_upload_dir();
        // установим имя файла = ID поста
        $f_name = $offer_id;
        $f_basedir = $path['basedir'];
        $f_baseurl = $path['baseurl'];

        file_put_contents( $f_basedir . '/' . $f_name, $img);

        $type_img = exif_imagetype($f_basedir . '/' . $f_name);
        switch ($type_img) {
            case 1:
                $file_extension = '.gif';
            break;
            case 2:
                $file_extension = '.jpeg';
            break;
            case 3:
                $file_extension = '.png';
            break;
            case 4:
                $file_extension = null; // swf
            break;
            case 5:
                $file_extension = null; // psd
            break;
            case 6:
                $file_extension = '.bmp';
            break;
            case 7:
                $file_extension = null; // tiff ii
            break;
            case 8:
                $file_extension = null; // tiff mm
            break;
            case 9:
                $file_extension = null; // jpc
            break;
            case 10:
                $file_extension = null; // jp2
            break;
            case 11:
                $file_extension = null; // jpx
            break;
            case 12:
                $file_extension = null; // jb2
            break;
            case 13:
                $file_extension = null; // swc
            break;
            case 14:
                $file_extension = null; // iff
            break;
            case 15:
                $file_extension = null; // wbmp
            break;
            case 16:
                $file_extension = null; // xbm
            break;
            case 17:
                $file_extension = null; // ico
            break;
            case 18:
                $file_extension = '.webp'; // 
            break;
        }

        if ($file_extension) {
            $f_full_name = $f_name . $file_extension;

            rename( $f_basedir . '/' . $f_name, $f_basedir . '/' . $f_full_name );
            
            $result_sideload = media_sideload_image($f_baseurl . '/' . $f_full_name, $offer_id, null, 'id');

            if( is_wp_error($result_sideload) ){
                $result['result_sideload'] = $result_sideload->get_error_message();
            }
            $result['f_full_name'] = $f_full_name;

            $result_thumb = set_post_thumbnail($offer_id, $result_sideload);
            if( is_wp_error($result_thumb) ){
                $result['resut_thumb'] = $result_thumb;
            }    
            unlink( $f_basedir . '/' . $f_full_name );

        } else {
            unlink( $f_basedir . '/' . $f_name );
        }
    }

    public function toggle_state_offer() {
        $result = [
            'message'   => '',
            'err'       => '',
            'result'    => true,
            'new_state' => ''
        ];

        if (!isset($_POST['id'])) {
            $result['message'] = "Дай мне id оффера, потом поговорим";
            $result['result'] = false;
            echo json_encode($result);
            exit;
        }
        $offer_id = $_POST['id'];
        $offer_name = get_the_title($offer_id);
        $offer_state = get_post_meta($offer_id, 'offer_state', 1); 
        switch ($offer_state) {
            case 'active':
                update_post_meta( $offer_id, 'offer_state', 'disable' );
                $result['message'] = $offer_name . ' -- оффер отключен';
                $result['new_state'] = 'disable';
            break;
            case 'disable':
                update_post_meta( $offer_id, 'offer_state', 'active' );
                $result['message'] = $offer_name . ' -- оффер включен';
                $result['new_state'] = 'active';
            break;
            default:
                update_post_meta( $offer_id, 'offer_state', 'disable' );
                $result['message'] = $offer_name . ' -- оффер отключен';
                $result['new_state'] = 'disable';
            break;
        }
        echo json_encode($result);
        exit;
        
    }

}
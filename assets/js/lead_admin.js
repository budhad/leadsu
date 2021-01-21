(function($) {

    var preloader = '<div class="preloader_container"><div class="circle circle-1"></div><div class="circle circle-2"></div><div class="circle circle-3"></div><div class="circle circle-4"></div><div class="circle circle-5"></div></div>';

    $(document).ready(function() {
        // создание категорий
        $('#create_offer_cats').on('click', function(e){
            e.preventDefault();

            show_preloader($('#create_offer_cats'));

            var formData = new FormData();
            formData.append('action', 'recreate_cats');

            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if ( result.result ) {
                    var text = "Добавлено категорий: " + result.new_cats_id.length;
                    show_message_admin_plugin('success', text);
                } else {
                    show_message_admin_plugin('error', result.message);
                }
                hide_preloader($('#create_offer_cats'));
            })
            .catch(err => {
                show_message_admin_plugin('error', err);
                hide_preloader($('#create_offer_cats'));
            });
        });
        // создание офферов
        $('#create_offers').on('click', function(e){
            e.preventDefault();

            show_preloader($('#create_offers'));

            // 
            // promise version

            var formData = new FormData();
            formData.append('action', 'download_offers');

            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (!result.result) {
                    show_message_admin_plugin('error', result.message + ' -- ' + result.err);
                } else {
                    show_message_admin_plugin('success', 'Получено офферов: ' + result.offers.length);

                    $.each(result.offers, function(index, offer){
                        var lastOffer = false;
                        
                        if (index == result.offers.length - 1) {
                            lastOffer = true;
                        }
                        createOffer(offer, index, lastOffer, true);
                    });
                }
            })
            .catch(err => {
                show_message_admin_plugin('error', err);
                hide_preloader($('#create_offers'));
            });

        });

        $('body').on('click', '#clear_area_message', function(e){
            $(e.target).siblings('.notice').remove();
            $(e.target).remove();
        });

        $('body').on('click', '.toggle_state_offer', function(e) {
            e.preventDefault();

            show_preloader($(e.target));

            var formData = new FormData();
            formData.append('action', 'toggle_state_offer');
            formData.append('id', $(e.target).data('offer_id'));

            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            })
            .then( response => response.json() )
            .then( result => {
                if (result.result) {
                    switch (result.new_state) {
                        case 'active':
                            $(e.target).addClass('active_offer');
                            $(e.target).removeClass('disable_offer');
                            $(e.target).text('Отключить');
                            $(e.target).siblings('span').text('Активен');
                            break;
                        case 'disable':
                            $(e.target).addClass('disable_offer');
                            $(e.target).removeClass('active_offer');
                            $(e.target).text('Включить');
                            $(e.target).siblings('span').text('Выключен');
                            break;
                    }                    

                }
                hide_preloader($(e.target));
            } )
            .catch( err => {
                console.log('При отключении что-то пошло не так: ' + err);
                hide_preloader($(e.target));
            } )
        });
    });


    function show_message_admin_plugin($type = 'info', $message = '', $this = null) {
        // классы сообщений:
        // notice-success - для успешных операций. Зеленая полоска слева.
        // notice-error - для ошибок. Красная полоска слева.
        // notice-warning - для предупреждений. Оранжевая полоска слева.
        // notice-info - для информации. Синяя полоска слева.
        // is-dismissible - добавляет иконку-кнопку "закрыть" (крестик в конце блока). Иконка добавляется через javascript. По клику на нее блок-заметка будет скрыт (удален), но это состояние не сохраняется, то есть при обновлении страницы блок снова будет отображаться.
        if (!$this) {
            $this = $('#lead_options_form');
        }
        var $button_clear = $('#clear_area_message');

        var html = "<div class='notice notice-" + $type + " is-dismissible'>";
        html += "<p>" + $message + "</p>";
        html += "</div>";

        if (!$button_clear.length) {
            $("<p><button id='clear_area_message' class='button button-secondary'>Очистить все сообщения</button></p><br/>").insertAfter($this);
            $button_clear = $('#clear_area_message');
        }

        $(html).insertAfter($button_clear);
    }

    function createOffer(offer, index = 1, lastOffer = false) {
        var formData = new FormData();
        formData.append('offer', JSON.stringify(offer));
        formData.append('action', 'create_offer');

        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (!result.result) {
                show_message_admin_plugin('error', index + ' - ' + result.message + ' : ' + result.err);
            } else {
                show_message_admin_plugin('success', index + ' - ' + result.message);
            }
            if (lastOffer) {
                hide_preloader($('#create_offers'));    
            }
        })
        .catch(err => {
            show_message_admin_plugin('error', err);
            hide_preloader($('#create_offers'));
        });        
    }
    
    function show_preloader($target) {
        // 
        if (!$target.length) return;

        $(preloader).insertAfter($target);
        $target.prop('disabled', true);
    }
    function hide_preloader($target) {
        // 
        if (!$target.length) return;
        $target.prop('disabled', false);
        $current_preoader = $target.siblings('.preloader_container');
        if (!$current_preoader.length) {
            console.log('Не найден прелоадер для удаления lead_admin');
        }
        $current_preoader.fadeOut(150, function() {
            $current_preoader.remove();
        })
    }

})(jQuery);

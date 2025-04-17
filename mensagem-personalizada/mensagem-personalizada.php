<?php
/*
Plugin Name: Mensagem Personalizada
Description: Permite definir uma mensagem personalizada pelo painel e exibir
Version: 1.7
Author: Rennan
*/

//1. Criar a pagina no admin
function mp_adicionar_menu_admin(){
    add_menu_page(
        'Mensagem Personalizada',
        'Mensagem',
        'manage_options',
        'mensagem-personalizada',
        'mp_pagina_opcoes',
        'dashicons-format-chat',
        20
    );
}

add_action('admin_menu', 'mp_adicionar_menu_admin');

// 2. Exibir o conteudo
function mp_pagina_opcoes(){
    ?>
    <div class="wrap">
        <h1>Mensagem Personalizada</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('mp_opcoes');
            do_settings_sections('mensagem-personalizada');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// 3. Registrar a configuração
//Quando for adicionar algo sempre fazer o registro da configuração
function mp_registrar_configuracao(){
    register_setting('mp_opcoes', 'mp_mensagem');
    register_setting('mp_opcoes', 'mp_imagem_extra');

    add_settings_section(
        'mp_secao',
        'Configuração da Mensagem',
        null,
        'mensagem-personalizada'
    );

    add_settings_field(
        'mp_mensagem',
        'Mensagem:',
        'mp_campo_mensagem_callback',
        'mensagem-personalizada',
        'mp_secao'
    );

    add_settings_field(
        'mp_imagem_extra',
        'Imagem Separada',
        'mp_campo_imagem_extra_callback',
        'mensagem-personalizada',
        'mp_secao'
    );
}

add_action('admin_init', 'mp_registrar_configuracao');

//4. Campo de input
function mp_campo_mensagem_callback(){
    $mensagem = get_option('mp_mensagem', '');
    
    wp_editor(
        $mensagem,
        'mp_mensagem',
        array(
            'textname_name' => 'mp_mensagem',
            'media_buttons' => false,
            'textarea_rows' => 10,
            'teeny'         => true //editor simplificado
        )
        );
}

function mp_campo_imagem_extra_callback() {
    $imagem = get_option('mp_imagem_extra');
    ?>
    <input type="text" name="mp_imagem_extra" id="mp_imagem_extra" value="<?php echo esc_attr($imagem); ?>" style="width: 60%;" />
    <button type="button" class="button" id="mp_selecionar_imagem">Selecionar Imagem</button>
    <div id="mp_preview_imagem" style="margin-top: 10px;">
        <?php if ($imagem): ?>
            <img src="<?php echo esc_url($imagem); ?>" style="max-width: 300px;" />
        <?php endif; ?>
    </div>

    <script>
        jQuery(document).ready(function($){
            $('#mp_selecionar_imagem').click(function(e){
                e.preventDefault();
                var frame = wp.media({
                    title: 'Escolher imagem',
                    button: { text: 'Usar imagem' },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#mp_imagem_extra').val(attachment.url);
                    $('#mp_preview_imagem').html('<img src="' + attachment.url + '" style="max-width: 300px;" />');
                });

                frame.open();
            });
        });
    </script>
    <?php
}


//5, Shortcode para exibir a mensagem
//wp_kses_post: permite um html seguro mas bloqueia scripts maliciosos
function mp_shortcode_mensagem() {
    $mensagem = get_option('mp_mensagem', 'Mensagem padrão.');
    return '<div style="padding:20px; border:1px solid #ccc; text-align:center;">' . wp_kses_post($mensagem). '</div>';
}

function mp_shortcode_imagem_personalizada() {
    $imagem_url = get_option('mp_imagem_extra');

    if (!$imagem_url) return '';

    return '<img src="' . esc_url($imagem_url) . '" alt="Imagem personalizada" style="max-width:100%; height:auto;" />';
}

add_shortcode('mensagem_personalizada', 'mp_shortcode_mensagem');
add_shortcode('imagem_personalizada', 'mp_shortcode_imagem_personalizada');
//add_action('wp-head' , 'mostrar')
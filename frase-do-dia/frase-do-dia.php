<?php
/*
Plugin Name: Frase do Dia
Description: Exibe uma frase diferente a cada dia via shortcode.
Version: 1.0
Author: Rennan
*/

// 1. Registrar configurações
function fd_registrar_configuracoes() {
    register_setting('fd_opcoes', 'fd_frases');

    add_settings_section(
        'fd_secao',
        'Frases do Dia',
        null,
        'frase-do-dia'
    );

    add_settings_field(
        'fd_frases',
        'Lista de Frases (uma por linha)',
        'fd_campo_frases_callback',
        'frase-do-dia',
        'fd_secao'
    );
}
add_action('admin_init', 'fd_registrar_configuracoes');

// 2. Campo de texto (callback)
function fd_campo_frases_callback() {
    $frases = get_option('fd_frases', '');
    echo '<textarea name="fd_frases" rows="10" cols="50" class="large-text code">' . esc_textarea($frases) . '</textarea>';
}

// 3. Adicionar menu no admin
function fd_adicionar_menu() {
    add_menu_page(
        'Frase do Dia',
        'Frase do Dia',
        'manage_options',
        'frase-do-dia',
        'fd_pagina_opcoes',
        'dashicons-format-quote',
        20
    );
}
add_action('admin_menu', 'fd_adicionar_menu');

// 4. Conteúdo da página de configuração
function fd_pagina_opcoes() {
    ?>
    <div class="wrap">
        <h1>Frase do Dia</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('fd_opcoes');
            do_settings_sections('frase-do-dia');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// 5. Shortcode para exibir frase
function fd_shortcode_frase() {
    $frases = explode("\n", get_option('fd_frases', ''));
    $frases = array_filter(array_map('trim', $frases));

    if (empty($frases)) return 'Nenhuma frase cadastrada.';

    $dia = date('z'); // dia do ano (0-365)
    $index = $dia % count($frases);

    return '<blockquote>' . esc_html($frases[$index]) . '</blockquote>';
}
add_shortcode('frase_do_dia', 'fd_shortcode_frase');

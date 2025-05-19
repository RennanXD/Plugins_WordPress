<?php
/*
Plugin Name: Player de Música
Description: Plugin para adicionar um player de música ao seu site WordPress.
Version: 2.1
Author: Rennan
*/

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'toplevel_page_player-musica') return;
    wp_enqueue_media();
});

function mp_player_musica_menu() {
    add_menu_page(
        'Player de Música',
        'Player de Música',
        'manage_options',
        'player-musica',
        'mp_player_musica_page',
        'dashicons-format-audio'
    );
}
add_action('admin_menu', 'mp_player_musica_menu');


function mp_player_musica_page() {
    ?>
    <div class="wrap">
        <h1>Configuração do Player de Música</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('mp_player_musica_options');
            do_settings_sections('player-musica');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function mp_register_settings() {
    register_setting('mp_player_musica_options', 'mp_musica_url');
    register_setting('mp_player_musica_options', 'mp_musicas_playlist');

    add_settings_section(
        'mp_player_musica_section',
        'Configuração do Player de Música',
        null,
        'player-musica'
    );

    add_settings_field(
        'mp_musica_url_field',
        'Selecionar Música:',
        'mp_musica_url_callback',
        'player-musica',
        'mp_player_musica_section'
    );

    add_settings_field('mp_musicas_playlist_field', 'Playlist de Músicas', 'mp_musicas_playlist_callback', 'player-musica', 'mp_player_musica_section');
}
add_action('admin_init', 'mp_register_settings');


function mp_musicas_playlist_callback(){
    $musicas = get_option('mp_musicas_playlist',[]);
    if (!is_array($musicas)) $musicas =[];

    ?>
    <div id="mp-musicas-container">
        <?php foreach($musicas as $musica):?>
            <div class="mp-musica-item">
            <input type="text" name="mp_musicas_playlist[]" value="<?php echo esc_url($musica); ?>" style="width:80%;" />
            <button type="button" class="button remove-musica">Remover</button>
            </div>
        <?php endforeach;?>
    </div>
    <button type="button" class="button" id="add-musica">Adicionar Música</button>
    <script>
         jQuery(document).ready(function($) {
        $('#add-musica').click(function(e) {
            e.preventDefault();
            var frame = wp.media({
                title: 'Selecionar música',
                button: { text: 'Usar esta música' },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                var html = '<div class="mp-musica-item">';
                html += '<input type="text" name="mp_musicas_playlist[]" value="' + attachment.url + '" style="width:80%;" />';
                html += '<button type="button" class="button remove-musica">Remover</button></div>';
                $('#mp-musicas-container').append(html);
            });

            frame.open();
        });

        $(document).on('click', '.remove-musica', function() {
            $(this).parent().remove();
        });
    });
    </script>
    <?php
}

#aqui adiciona as musicas
function mp_musica_url_callback() {
    $musica_url = get_option('mp_musica_url');
    ?>
    <input type="text" name="mp_musica_url" id="mp_musica_url" value="<?php echo esc_attr($musica_url); ?>" style="width: 60%;" />
    <button type="button" class="button" id="mp_selecionar_musica">Selecionar Música</button>

    <script>
        jQuery(document).ready(function($){
            $('#mp_selecionar_musica').click(function(e){
                e.preventDefault();
                var frame = wp.media({
                    title: 'Escolher Música',
                    button: { text: 'Usar esta música' },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#mp_musica_url').val(attachment.url);
                });

                frame.open();
            });
        });
    </script>
    <?php
}

function mp_carregar_scripts_midia($hook) {
    if ($hook !== 'toplevel_page_player-musica') return;
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'mp_carregar_scripts_midia');

function mp_exibir_player_musica() {
    $musica_url = get_option('mp_musica_url');
    $playlist = get_option('mp_musicas_playlist', []);

    // Verifica se há músicas na playlist
    if (empty($playlist) || !is_array($playlist)) {
        return '<p>Nenhuma música configurada na playlist.</p>';
    }

    ob_start();
    ?>
        <div class="mp-player">
        <audio id="player-audio">
            <source id="audio-source" src="<?php echo esc_url($playlist[0]); ?>" type="audio/mpeg">
            Seu navegador não suporta o elemento de áudio.
        </audio>

        <div class="mp-controls">
            <button id="mp-prev">«</button>
            <button id="mp-play">▶</button>
            <button id="mp-pause">❚❚</button>
            <button id="mp-next">»</button>
        </div>

        <div id="mp-current-title" style="margin-top: 10px; font-weight: bold;">
            <?php echo basename($playlist[0]); ?>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const playlist = <?php echo json_encode($playlist); ?>;
                let currentTrack = 0;

                const audio = document.getElementById('player-audio');
                const source = document.getElementById('audio-source');
                const title = document.getElementById('mp-current-title');

                function loadTrack(index) {
                    if (playlist[index]) {
                        source.src = playlist[index];
                        audio.load();
                        title.textContent = playlist[index].split('/').pop();
                        audio.play();
                    }
                }

                document.getElementById('mp-play').addEventListener('click', () => {
                    audio.play();
                });

                document.getElementById('mp-pause').addEventListener('click', () => {
                    audio.pause();
                });

                document.getElementById('mp-prev').addEventListener('click', () => {
                    currentTrack = (currentTrack - 1 + playlist.length) % playlist.length;
                    loadTrack(currentTrack);
                });

                document.getElementById('mp-next').addEventListener('click', () => {
                    currentTrack = (currentTrack + 1) % playlist.length;
                    loadTrack(currentTrack);
                });

                // Auto next when track ends
                audio.addEventListener('ended', () => {
                    currentTrack = (currentTrack + 1) % playlist.length;
                    loadTrack(currentTrack);
                });
            });
        </script>
    </div>
    <?php
    return ob_get_clean();
}


function mp_carregar_scripts_player() {
    wp_enqueue_style('mp-player-musica-css', plugin_dir_url(__FILE__) . 'css/player.css');
    wp_enqueue_script('mp-player-musica-js', plugin_dir_url(__FILE__) . 'js/player.js', array(), null, true);
}

add_action('wp_enqueue_scripts', 'mp_carregar_scripts_player');

add_shortcode('player_musica', 'mp_exibir_player_musica');
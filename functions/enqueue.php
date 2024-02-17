<?php

function enqueue_font_awesome()
{
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', [], null);
}

add_action('wp_enqueue_scripts', 'enqueue_font_awesome');

function hide_editor()
{
    // Check if you are on the post editing screen for the desired post type(s).
    global $post;

    // Replace 'post' with the post type you want to hide the editor for (e.g., 'page' for pages).
    if ($post->post_type === 'camps') {
        echo '<style>#postdivrich { display: none; }</style>';
    }
}

add_action('edit_form_after_editor', 'hide_editor');

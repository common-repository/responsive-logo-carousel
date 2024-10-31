<?php
/*
  Plugin Name: Responsive Logo Carousel
  Description: A simple plugin to include a responsive carousel customized for logos in any post by using a simplified shortcode.
  Author: Dennis Muriu
  Version: 1.0.0
  Author URI: #
  Licence: GPL2
 */
function rlc_custom_post_logo() {
  $labels = array(
    'name'               => _x( 'Logos', 'post type general name' ),
    'singular_name'      => _x( 'Logo', 'post type singular name' ),
    'add_new'            => _x( 'Add New', 'logo' ),
    'add_new_item'       => __( 'Add New Logo' ),
    'edit_item'          => __( 'Edit Logo' ),
    'new_item'           => __( 'New Logo' ),
    'all_items'          => __( 'All Logos' ),
    'view_item'          => __( 'View Logos' ),
    'search_items'       => __( 'Search Logos' ),
    'not_found'          => __( 'No logos found' ),
    'not_found_in_trash' => __( 'No logos found in the Trash' ), 
    'parent_item_colon'  => '',
    'menu_name'          => 'Responsive Logo Carousel'
  );
  $args = array(
    'labels'        => $labels,
    'description'   => 'Holds our logos and logo specific data',
    'public'        => true,
    'capability_type' => 'post',
    'menu_position' => 5,
    'menu_icon' => plugins_url('images/rl1.png', __FILE__),
    'supports'      => array( 'title', 'thumbnail' ),
    'has_archive'   => true,
  );
  register_post_type( 'lgs', $args ); 
  $taxonomy_labels = array(
        'name' => __('Logo Carousels'),
        'singular_name' => __('Logo Carousel'),
        'search_items' => __('Search Carousels'),
        'popular_items' => __('Popular Carousels'),
        'all_items' => __('All Carousels'),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __('Edit Carousel'),
        'update_item' => __('Update Carousel'),
        'add_new_item' => __('Add New Carousel'),
        'new_item_name' => __('New Carousel Name'),
        'separate_items_with_commas' => __('Separate carousels with commas'),
        'add_or_remove_items' => __('Add or remove carousels'),
        'choose_from_most_used' => __('Choose from the most used carousels'),
        'not_found' => __('No carousels found.'),
        'menu_name' => __('Carousels'),
    );

    register_taxonomy('Logo Carousel','lgs', array(
        'labels' => $taxonomy_labels,
        'rewrite' => array('slug' => 'l-carousel'),
        'hierarchical' => true,
        'show_admin_column' => true,
    ));   
}
add_action( 'init', 'rlc_custom_post_logo' );
add_shortcode('responsive-logo-carousel', 'rlc_responsive_logo_function');
add_option('rlc_carousel_orderby', 'post_date');
add_action('wp_print_styles', 'rlc_logo_carousel_styles');
add_action('wp_print_scripts', 'rlc_logo_carousel_js');
add_option('lgs_wordpress_gallery', 'off');

add_action('manage_edit-lgs_columns', 'rlc_lgs_columnfilter');
add_action('manage_posts_custom_column', 'rlc_lgs_column');
//add_filter("mce_external_plugins", "owl_register_tinymce_plugin");
add_filter("mce_external_plugins", "rlc_tinymce_plugin_reg");
add_filter('mce_buttons', 'rlc_tinymce_button_add');

function rlc_responsive_logo_function($atts, $content = null) {
    extract(shortcode_atts(array(
        'category' => 'Uncategoryzed'
                    ), $atts));

    $data_attr = "";
    foreach ($atts as $key => $value) {
        if ($key != "category") {
            $data_attr .= ' data-' . $key . '="' . $value . '" ';
        }
    }

    $args = array(
        'post_type' => 'lgs',
        'orderby' => get_option('rlc_carousel_orderby', 'post_date'),
        'order' => 'asc',
        'tax_query' => array(
            array(
                'taxonomy' => 'Logo Carousel',
                'field' => 'slug',
                'terms' => $atts['category']
            )
        ),
        'nopaging' => true
    );
    $loop = new WP_Query($args);
    if($loop->have_posts()){
        $result = '<div id="demo">
              <div class="customNavigation">
                <a id="myPrev" class="prev" style="cursor:pointer">&#9665;</a>
                <a id="myNext" class="next" style="cursor:pointer">&#9655;</a>
              </div>';
              $result .= '<div id="owl-demo" class="owl-carousel">';
    }
    while ($loop->have_posts()) {
        $loop->the_post();

        $img_src = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), get_post_type());

        $result .= '<div class="item">';
        if ($img_src[0]) {
            $result .= '<div>';           
          
                $result .= '<img title="' . get_the_title() . '" src="' . $img_src[0] . '" alt="' . get_the_title() . '"/>';           
            // Add image overlay with hook
            $result .= '</div>';
        } else {
            $result .= '<div class="owl-carousel-item-text">' . get_the_content() . '</div>';
        }
        $result .= '</div>';
    }
    $result .= '</div>';

    return $result;
  }
//Enque all the CSS used
function rlc_logo_carousel_styles() {
    wp_register_style('style.custom', plugins_url('assets/css/custom.css', __FILE__));
    wp_register_style('style.carousel.css', plugins_url('owl-carousel/owl.carousel.css', __FILE__));

    wp_enqueue_style('style.custom');
    wp_enqueue_style('style.carousel.css');
}
//Enque scripts used
function rlc_logo_carousel_js() {
    wp_register_script('owl.carousel.js', plugins_url('owl-carousel/owl.carousel.js', __FILE__));
    wp_register_script('application.js', plugins_url('assets/js/application.js', __FILE__));

    wp_enqueue_script('jquery');
    wp_enqueue_script('owl.carousel.js');
    wp_enqueue_script('application.js');
    
}
//Custom column in posts list
function rlc_lgs_columnfilter($columns) {
    $thumb = array('thumbnail' => 'Image');
    $columns = array_slice($columns, 0, 2) + $thumb + array_slice($columns, 2, null);

    return $columns;
}
//Listing in admin section
function rlc_lgs_column($columnName) {
    global $post;
    if ($columnName == 'thumbnail') {
        echo edit_post_link(get_the_post_thumbnail($post->ID, 'thumbnail'), null, null, $post->ID);
    }
}

function rlc_tinymce_plugin_reg($plugin_array) {
    $plugin_array['rlc_button'] = plugins_url('owl-carousel/owl-tinymce-plugin.js', __FILE__);
    return $plugin_array;
}

function rlc_tinymce_button_add($buttons) {
    $buttons[] = "rlc_button";
    return $buttons;
}

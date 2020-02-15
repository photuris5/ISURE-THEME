<?php

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 */

if ( ! defined('MATERIALIS_THEME_REQUIRED_PHP_VERSION')) {
    define('MATERIALIS_THEME_REQUIRED_PHP_VERSION', '5.3.0');
}

add_action('after_switch_theme', 'materialis_check_php_version');

function materialis_check_php_version()
{
    // Compare versions.
    if (version_compare(phpversion(), MATERIALIS_THEME_REQUIRED_PHP_VERSION, '<')) :
        // Theme not activated info message.
        add_action('admin_notices', 'materialis_php_version_notice');


        // Switch back to previous theme.
        switch_theme(get_option('theme_switched'));

        return false;
    endif;
}

function materialis_php_version_notice()
{
    ?>
    <div class="notice notice-alt notice-error notice-large">
        <h4><?php esc_html_e('Materialis theme activation failed!', 'materialis'); ?></h4>
        <p>
            <?php printf(esc_html__('You need to update your PHP version to use the %s.', 'materialis'), "<strong>Materialis</strong>"); ?> <br/>
            <?php printf(
            // Translators: 1 is the users PHP version and 2 is the required PHP version.
                esc_html__('Current PHP version is %1$s and the minimum required version is %2$s', 'materialis'),
                '<strong>' . phpversion() . '</strong>',
                '<strong>' . MATERIALIS_THEME_REQUIRED_PHP_VERSION . '</strong>'
            ); ?>
        </p>
    </div>
    <?php
}

if (version_compare(phpversion(), MATERIALIS_THEME_REQUIRED_PHP_VERSION, '>=')) {
    require_once get_template_directory() . "/inc/functions.php";

    //SKIP FREE START


    if ( ! defined('MATERIALIS_ONLY_FREE') || ! MATERIALIS_ONLY_FREE) {
        // NEXT FREE VERSION
        require_once get_template_directory() . "/inc/functions-next.php";

        // PRO HERE
        require_once get_template_directory() . "/pro/functions.php";
    }

    //SKIP FREE END

    if ( ! materialis_can_show_cached_value("materialis_cached_kirki_style_materialis")) {
        
        if ( ! materialis_skip_customize_register()) {
            do_action("materialis_customize_register_options");
        }
    }

} else {
    add_action('admin_notices', 'materialis_php_version_notice');
}


//authorBlock
//function isureauthor_register_template() {
//    $post_type_object = get_post_type_object( 'post' );
//    $post_type_object->template = array(
//        array( 'coblocks/author' ),
//    );
//}
//add_action( 'init', 'isureauthor_register_template' );

//changeTitle
function wpb_change_title_text( $title ){
     $screen = get_current_screen();
  
     if  ( 'post' == $screen->post_type ) {
          $title = 'Enter project name here';
     }
  
     return $title;
}
  
add_filter( 'enter_title_here', 'wpb_change_title_text' );

//AuthorPostsBox
//From Gina
function wpb_author_info_box( $content ) {
  
global $post;
  
// Detect if it is a single post with a post author
if ( is_single() && isset( $post->post_author ) ) {
  
// Get author's display name 
$display_name = get_the_author_meta( 'display_name', $post->post_author );
  
// If display name is not available then use nickname as display name
if ( empty( $display_name ) )
$display_name = get_the_author_meta( 'nickname', $post->post_author );
  
// Get author's biographical information or description
$user_description = get_the_author_meta( 'user_description', $post->post_author );
  
// Get author's website URL 
$user_website = get_the_author_meta('url', $post->post_author);
  
// Get link to the author archive page
$user_posts = get_author_posts_url( get_the_author_meta( 'ID' , $post->post_author));
   
if ( ! empty( $display_name ) )
  
$author_details = '<p class="author_name">About ' . $display_name . '</p>';
  
if ( ! empty( $user_description ) )
// Author avatar and bio
  
$author_details .= '<p class="author_details">' . get_avatar( get_the_author_meta('user_email') , 90 ) . nl2br( $user_description ). '</p>';
  
$author_details .= '<p class="author_links"><a href="'. $user_posts .'">View all posts by ' . $display_name . '</a>';  
  
// Check if author has a website in their profile
if ( ! empty( $user_website ) ) {
  
// Display author website link
$author_details .= ' | <a href="' . $user_website .'" target="_blank" rel="nofollow">Website</a></p>';
  
} else { 
// if there is no author website then just close the paragraph
$author_details .= '</p>';
}
  
// Pass all this info to post content  
$content = $content . '<footer class="author_bio_section" >' . $author_details . '</footer>';
}
return $content;
}
  
// Add our function to the post content filter 
add_action( 'the_content', 'wpb_author_info_box' );
  
// Allow HTML in author bio section 
remove_filter('pre_user_description', 'wp_filter_kses');
<?php

// add_action( 'init', 'register_acf_blocks' );

// function register_acf_blocks() {
// register_block_type( __DIR__ . '/blocks/content' );
// register_block_type( __DIR__ . '/blocks/applications' );
// register_block_type( __DIR__ . '/blocks/about' );
// // register_block_type( __DIR__ . '/blocks/industries' );
// // register_block_type( __DIR__ . '/blocks/popup' );
// // register_block_type( __DIR__ . '/blocks/gallery' );
// // register_block_type( __DIR__ . '/blocks/testimonials' );
// // register_block_type( __DIR__ . '/blocks/gallery-carousel' );
// // register_block_type( __DIR__ . '/blocks/tabs' );
// }

function naiely_miranda_stylesheets() {
wp_enqueue_style('style', get_stylesheet_uri() );

wp_enqueue_style('layout', get_theme_file_uri('/css/sections/layout.css'));
wp_enqueue_style('body', get_theme_file_uri('/css/sections/body.css'));
wp_enqueue_style('nav', get_theme_file_uri('/css/sections/nav.css'));
wp_enqueue_style('popup', get_theme_file_uri('/css/sections/popup.css'));
wp_enqueue_style('hero', get_theme_file_uri('/css/sections/hero.css'));
wp_enqueue_style('contact', get_theme_file_uri('/css/sections/contact.css'));
wp_enqueue_style('img', get_theme_file_uri('/css/elements/img.css'));

// if(is_front_page()){
wp_enqueue_style('home', get_theme_file_uri('/css/sections/home.css'));
// }
if(is_page_template('templates/about.php')){
wp_enqueue_style('about-custom', get_theme_file_uri('/css/sections/about.css'));
wp_enqueue_style('intro', get_theme_file_uri('/css/sections/intro.css'));
}
if( is_page_template('templates/content-page.php' ) ){
wp_enqueue_style('content-page', get_theme_file_uri('/css/sections/content-page.css'));
}
if(is_single() || is_page_template('templates/blog.php') || is_archive() || is_category() || is_tag() || is_404() ) {
wp_enqueue_style('blog', get_theme_file_uri('/css/sections/blog.css'));
}

wp_enqueue_style('photo-gallery', get_theme_file_uri('/css/sections/photo-gallery.css'));
wp_enqueue_style('gutenberg-custom', get_theme_file_uri('/css/sections/gutenberg.css'));
wp_enqueue_style('footer', get_theme_file_uri('/css/sections/footer.css'));
wp_enqueue_style('sidebar', get_theme_file_uri('/css/sections/sidebar.css'));
wp_enqueue_style('social-icons', get_theme_file_uri('/css/sections/social-icons.css'));
wp_enqueue_style('btn', get_theme_file_uri('/css/elements/btn.css'));
// fonts
wp_enqueue_style('fonts', get_theme_file_uri('/css/elements/fonts.css'));
// wp_enqueue_style('proxima-nova', get_theme_file_uri('/proxima-nova/proxima-nova.css'));
// wp_enqueue_style('blair-itc', get_theme_file_uri('/blair-itc/blair-itc.css'));
// wp_enqueue_style('aspira', get_theme_file_uri('/aspira-font/aspira-font.css'));
wp_enqueue_style('font-poppins', get_theme_file_uri('/font-poppins/font-poppins.css'));
// wp_enqueue_style('coromant-garamond', '//use.typekit.net/fqe2slt.css');

}
add_action('wp_enqueue_scripts', 'naiely_miranda_stylesheets');
// for footer
function naiely_miranda_stylesheets_footer() {
// wp_enqueue_style('style-footer', get_theme_file_uri('/css/style-footer.css'));
// owl carousel
// wp_enqueue_style('owl.carousel.min', get_theme_file_uri('/owl-carousel/owl.carousel.min.css'));
// wp_enqueue_style('owl.theme.default', get_theme_file_uri('/owl-carousel/owl.theme.default.min.css'));
// wp_enqueue_style('lightbox-css', get_theme_file_uri('/lightbox/lightbox.min.css'));
// wp_enqueue_script('font-awesome', '//use.fontawesome.com/fff80caa08.js');

// owl carousel
// wp_enqueue_script('jquery-min', get_theme_file_uri('/owl-carousel/jquery.min.js'));
// wp_enqueue_script('owl-carousel', get_theme_file_uri('/owl-carousel/owl.carousel.min.js'));
// wp_enqueue_script('owl-carousel-custom', get_theme_file_uri('/owl-carousel/owl-carousels.js'));
// wp_enqueue_script('lightbox-min-js', get_theme_file_uri('/lightbox/lightbox.min.js'));
// wp_enqueue_script('lightbox-js', get_theme_file_uri('/lightbox/lightbox.js'));
// aos
wp_enqueue_script('aos-js', get_theme_file_uri('/aos/aos.js'));
wp_enqueue_script('aos-custom-js', get_theme_file_uri('/aos/aos-custom.js'));
wp_enqueue_style('aos-css', get_theme_file_uri('/aos/aos.css'));

// jquery fittext
// wp_enqueue_script('jquery-min-js', get_theme_file_uri('/jquery-fittext/jquery.min.js'));
// wp_enqueue_script('jquery-fittext', get_theme_file_uri('/jquery-fittext/jquery.fittext.js'));
// wp_enqueue_script('jquery-fittext-custom', get_theme_file_uri('/jquery-fittext/fittext.js'));
// jquery modal
// wp_enqueue_script('jquery-modal-js', get_theme_file_uri('/jquery-modal/jquery.modal.min.js'));
// wp_enqueue_style('jquery-modal-css', get_theme_file_uri('/jquery-modal/jquery.modal.min.css'));
// wp_enqueue_style('custom-modal', get_theme_file_uri('/jquery-modal/modal-custom.css'));
// general
wp_enqueue_script('nav-js', get_theme_file_uri('/js/nav.js'));
wp_enqueue_script('popup-js', get_theme_file_uri('/js/popup.js'));

if(is_single()){
	wp_enqueue_script('blog-js', get_theme_file_uri('/js/blog.js'));
	}
}

add_action('get_footer', 'naiely_miranda_stylesheets_footer');

// loads enqueued javascript files deferred
function mind_defer_scripts( $tag, $handle, $src ) {
$defer = array( 
	'jquery-min',
	'owl-carousel',
	'owl-carousel-custom',
	'lightbox-min-js',
	'lightbox-js',
	'aos-js',
	'aos-custom-js',
	'nav-js',
	'blog-js',
	'contact-js'
);
if ( in_array( $handle, $defer ) ) {
	return '<script src="' . $src . '" defer="defer" type="text/javascript"></script>' . "\n";
}
	
	return $tag;
} 
add_filter( 'script_loader_tag', 'mind_defer_scripts', 10, 3 );

function naiely_miranda_menus() {
register_nav_menus( array(
'primary' => __( 'Primary' )));
register_nav_menus( array(
'secondary' => __( 'Secondary' )));
register_nav_menu('footer',__( 'Footer' ));
add_theme_support('title-tag');
add_theme_support('post-thumbnails');
}

add_action('after_setup_theme', 'naiely_miranda_menus');

if( function_exists('acf_add_options_page') ) {

acf_add_options_page();
}

// removes gutenberg styles from all pages but the blog posts
function smartwp_remove_wp_block_library_css(){

if(!is_single()) {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'wc-block-style' ); // Remove WooCommerce block CSS
}
} 
add_action( 'wp_enqueue_scripts', 'smartwp_remove_wp_block_library_css', 100 );

// add_filter('show_admin_bar', '__return_false');

// add_filter('comment_form_default_fields', 'remove_website_field_from_comment_form');
function remove_website_field_from_comment_form($fields)
{
if (isset($fields['url'])) {
	unset($fields['url']);
}
return $fields;
}

/*Base URL shorcode*/
add_shortcode( 'base_url', 'baseurl_shortcode' );
function baseurl_shortcode( $atts ) {

return site_url();
// [base_url]

}

function divider_shortcode( $atts, $content = null ) {

$a = shortcode_atts( array(

	'class' => '',

	'style' => ''

), $atts );

return '<div class="divider ' . esc_attr($a['class']) . '" style="' . esc_attr($a['style']) . '"></div>';

// [divider class="" style=""]
}

add_shortcode( 'divider', 'divider_shortcode' );

function btn_shortcode( $atts, $content = null ) {

$a = shortcode_atts( array(

'class' => '',

'href' => '',

'style' => '',

'target' => '',

'id' => '',

'aria-label' => ''

), $atts );

$id = esc_attr($a['id']);

// Check if the ID contains the word "modal"
if (strpos($id, 'modal') !== false) {
	return '<span class="btn-main ' . esc_attr($a['class']) . '" aria-label="' . esc_attr($a['aria-label']) . '" style="' . esc_attr($a['style']) . '" target="' . esc_attr($a['target']) . '" id="' . esc_attr($a['id']) . '">' . $content . '</span>';
} else {
	return '<a class="btn-main ' . esc_attr($a['class']) . '" href="' . esc_attr($a['href']) . '" style="' . esc_attr($a['style']) . '" target="' . esc_attr($a['target']) . '" id="' . esc_attr($a['id']) . '">' . $content . '</a>';
}

// [button href="#" class="btn-main" style=""]Learn More[/button]

}

add_shortcode( 'button', 'btn_shortcode' );

function spacer_shortcode( $atts, $content = null ) {

$a = shortcode_atts( array(

	'class' => '',

	'style' => ''

), $atts );

return '<div class="spacer ' . esc_attr($a['class']) . '" style="' . esc_attr($a['style']) . '"></div>';

// [spacer class="" style=""]
}

add_shortcode( 'spacer', 'spacer_shortcode' );

function current_year( $atts, $content = null ) {

$current_year = date("Y");

return $current_year;

// [currentyear]
}

add_shortcode( 'currentyear', 'current_year' );

function social_media_icons( $atts, $content = null ) {

$socialIcons = '';

if(have_rows('social_icons','options')): 
	$socialIcons .= '<div class="si d-flex flex-wrap justify-content-end">';
	while(have_rows('social_icons','options')): the_row(); 
$svgOrImg = get_sub_field('svg_or_image');
$socialLink = get_sub_field('link');
$svg = get_sub_field('svg');
$image = get_sub_field('image');

$socialLink_url = $socialLink['url'];
$socialLink_title = $socialLink['title'];
$socialLink_target = $socialLink['target'] ? $socialLink['target'] : '_self';

$socialIcons .= '<a href="' . $socialLink_url . '" target="' . $socialLink_target . '" style="text-decoration:none;" class="si-icon-link">';

if($svgOrImg == 'SVG') {

	$socialIcons .= '<div class="svg-icon">';
	$socialIcons .= $svg;
	$socialIcons .= '</div>';
} elseif($svgOrImg == 'Image') {

	$socialIcons .= wp_get_attachment_image($image['id'],'full','',['class'=>'img-si']);

}
$socialIcons .= '</a>';

endwhile; 

$socialIcons .= '</div>';
endif; 

// return $socialIcons;

return get_template_part('partials/si');

// [social_icons class="" style=""]

}

add_shortcode( 'social_icons', 'social_media_icons' );

function my_page_title_shortcode() {
return get_the_title();
// [page_title]
}
add_shortcode('page_title', 'my_page_title_shortcode');

function my_phone_number() {
return get_field('phone','options');
// [phone_number]
}
add_shortcode('phone_number', 'my_phone_number');

function type_writer_shortcode( $atts ) {
	wp_enqueue_script('typewriter-js',get_theme_file_uri('/js/typewriter.js'));
	wp_enqueue_style('typewriter-css',get_theme_file_uri('/css/sections/typewriter.css'));
    // Extract shortcode attributes
    $atts = shortcode_atts( array(
        'text' => '',
        'wait' => '1000',
        'words' => '',
		'style'=>'',
		'class'=>''
    ), $atts );

    // Sanitize attribute values
    $text = sanitize_text_field( $atts['text'] );
    $wait = intval( $atts['wait'] );
    $word_sets = array_map( 'trim', explode( ',', $atts['words'] ) );

    // Output HTML
    ob_start();
	echo '<div class="' . esc_attr($atts['class']) . '" style="' . esc_attr($atts['style']) . '">';
    ?>
	<span><?php echo esc_html( $text ); ?></span><span class="txt-type" style="" data-wait="<?php echo esc_attr( $wait ); ?>" data-words='<?php echo esc_attr( json_encode( $word_sets ) ); ?>'></span>
	</div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'type_writer', 'type_writer_shortcode' );



function txt_type_shortcode( $atts ) {
    // Extract shortcode attributes
    $atts = shortcode_atts( array(
        'wait' => '1000',
        'words' => '["Developer","Designer","Creator"]'
    ), $atts );

    // Sanitize attribute values
    $wait = intval( $atts['wait'] );
    $words = json_decode( $atts['words'] );

    // Output HTML
    ob_start();
    ?>
    <span class="txt-type" data-wait="<?php echo esc_attr( $wait ); ?>" data-words='<?php echo esc_attr( json_encode( $words ) ); ?>'></span>
    <?php
    return ob_get_clean();
}
add_shortcode( 'txt_type', 'txt_type_shortcode' );


function custom_modify_block_output($block_content, $block) {
// Check if it's the core/paragraph, core/image, or core/columns block
if (in_array($block['blockName'], array('core/image', 'core/columns', 'core/quote'))) {
	// Modify the block content as needed
	$block_content = '<section class=""><div class="container"><div class="row"><div class="col-12">' . $block_content . '</div></div></div></section>';
}
return $block_content;
}

add_filter('render_block', 'custom_modify_block_output', 10, 2);

// function custom_modify_block_output($block_content, $block) {
//     global $post;

//     // Check if it's the core/paragraph, core/image, or core/columns block
//     if (
//         in_array($block['blockName'], array('core/paragraph', 'core/image', 'core/columns'))
//         && !has_block('core/quote', $post)
//     ) {
//         // Modify the block content as needed
//         $block_content = '<section class=""><div class="container"><div class="row"><div class="col-12">' . $block_content . '</div></div></div></section>';
//     }
//     return $block_content;
// }

// add_filter('render_block', 'custom_modify_block_output', 10, 2);


// Control core classes for avoid errors
if( class_exists( 'CSF' ) ) {

//
// Set a unique slug-like ID
$prefix = 'my_framework';

$post_prefix = 'my_post_framework';
// Create a metabox
CSF::createMetabox( $post_prefix, array(
    'title'     => 'My Post Options',
    'post_type' => 'post',
    'fields'    => array(
        array(
            'id'    => 'my_text_field',
            'type'  => 'text',
            'title' => 'My Text Field',
        ),
    ),
    'callback'  => 'my_metabox_callback_function',
) );

// Define the callback function
function my_metabox_callback_function() {
    echo 'This is where you can add additional HTML content for the metabox if needed.';
}

// Create options
CSF::createOptions( $prefix, array(
	'menu_title' => 'Global Settings',
	'menu_slug'  => 'my-framework',
) );

//
// Create a section
CSF::createSection( $prefix, array(
	'title'  => 'Logo',
	'fields' => array(

	//
	// A text field
	// array(
	// 	'id'    => 'opt-text',
	// 	'type'  => 'text',
	// 	'title' => 'Simple Text',
	// ),
	// Media
	array(
		'id'    => 'img-logo',
		'type'  => 'media',
		'title' => 'Main Logo',
		),
	// Code Editor
	array(
		'id'    => 'logo-svg',
		'type'  => 'code_editor',
		'title' => 'SVG for Logo',
		'sanitize' => false,
	  ),
		
		

	)
) );

//
// Create a section
CSF::createSection( $prefix, array(
	'title'  => 'About',
	'fields' => array(

	  
	  // A textarea field
      array(
        'id'    => 'company-about',
        'type'  => 'textarea',
        'title' => 'Description about the company',
      ),
	  

	)
) );
// Create a section
CSF::createSection( $prefix, array(
	'title'  => 'Header, Body & Footer Code',
	'fields' => array(

	//   array(
	// 	'id'       => 'code-header-one',
	// 	'type'     => 'code_editor',
	// 	'title'    => 'HTML Editor',
	// 	'sanitize' => false,
	// 	'settings' => array(
	// 	  'theme'  => 'mdn-like',
	// 	  'mode'   => 'htmlmixed',
	// 	),
	// 	'default'  => '<h1>Hello world</h1>',
	//   ),
	  array(
		'id'       => 'code-header',
		'type'     => 'code_editor',
		'title'    => 'Code: Header',
		'sanitize' => false,
	  ),
	  array(
		'id'       => 'code-body',
		'type'     => 'code_editor',
		'title'    => 'Code: Body',
		'sanitize' => false,
	  ),
	  array(
		'id'       => 'code-footer',
		'type'     => 'code_editor',
		'title'    => 'Code: Footer',
		'sanitize' => false,
	  ),
	  

	)
) );

}


function global_function() {
    global $options;
    $options = get_option( 'my_framework' ); // unique id of the framework
    return $options;
}

function logoImg() {
    global $options;
    global_function(); // call the global function to set $options
    return $options['img-logo'];
}
function logoSVG() {
    global $options;
    global_function(); // call the global function to set $options
    return $options['logo-svg'];
}
// function codeHeader() {
//     global $options;
//     global_function(); // call the global function to set $options
//     return $options['code-header-one'];
// }
function codeHeader() {
    global $options;
    global_function(); // call the global function to set $options
    return $options['code-header'];
}
function codeBody() {
    global $options;
    global_function(); // call the global function to set $options
    return $options['code-body'];
}
function codeFooter() {
    global $options;
    global_function(); // call the global function to set $options
    return $options['code-footer'];
}
function companyAbout() {
    global $options;
    global_function(); // call the global function to set $options
    return $options['company-about'];
}

// Allow SVG
add_filter( 'wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {

	global $wp_version;
	if ( $wp_version !== '4.7.1' ) {
	   return $data;
	}
  
	$filetype = wp_check_filetype( $filename, $mimes );
  
	return [
		'ext'             => $filetype['ext'],
		'type'            => $filetype['type'],
		'proper_filename' => $data['proper_filename']
	];
  
  }, 10, 4 );
  
  function cc_mime_types( $mimes ){
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
  }
  add_filter( 'upload_mimes', 'cc_mime_types' );
  
  function fix_svg() {
	echo '<style type="text/css">
		  .attachment-266x266, .thumbnail img {
			   width: 100% !important;
			   height: auto !important;
		  }
		  </style>';
  }
  add_action( 'admin_head', 'fix_svg' );
<?php

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
        
        // A textarea field
        array(
        'id'    => 'company-message',
        'type'  => 'textarea',
        'title' => 'Promotions going on in shop.',
        ),

        // repeater field
        array(
        'id'     => 'opt-repeater-1',
        'type'   => 'repeater',
        'title'  => 'Repeater',
        'sanitize' => false,
        'fields' => array(

            array(
            'id'    => 'opt-link-1',
            'type'  => 'link',
            'title' => 'Link',
            ),
            // Code Editor
        array(
            'id'    => 'svg-social-logos',
            'type'  => 'code_editor',
            'title' => 'SVG Social Logos',
            'sanitize' => false,
        ),
            
        
        ),
        ),
        // end of repeater field
        

    )
) );
// Create a section
CSF::createSection( $prefix, array(
    'title'  => 'Header, Body & Footer Code',
    'fields' => array(

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
function socialIconsRepeater() {
    global $options;
    global_function(); // call the global function to set $options
    return $options['opt-repeater-1'];
}

?>
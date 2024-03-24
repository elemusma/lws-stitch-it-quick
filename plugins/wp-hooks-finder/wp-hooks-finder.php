<?php
/**
 * Plugin Name: WP Hooks Finder
 * Version: 1.2.4
 * Description: Easily enable/disable hooks and filters which are running in the page. A menu "WP Hooks Finder" will be added in your wordpress admin bar menu where you can display all the hooks and filters
 * Author: Muhammad Rehman
 * Author URI: https://muhammadrehman.com/
 * License: GPLv2 or later
 * Text Domain: wphf_domain
 */

define( 'WPHF_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );

/**
 * Adding style
 * 
 * @since 1.0
 * @version 1.0
 */
function wphf_style() {
    wp_enqueue_style( 'wphf-style', WPHF_PLUGIN_PATH . 'assets/css/style.css' );    
}
add_action( 'wp_enqueue_scripts', 'wphf_style' );
add_action( 'admin_enqueue_scripts', 'wphf_style' );

/**
 * Adding menu in the Admin Bar Menu
 * 
 * @since 1.0
 * @version 1.2
 */
add_action('admin_bar_menu', 'wphf_add_toolbar_items', 99 );

function wphf_add_toolbar_items( $admin_bar ){

    if( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $page_url = wphf_clean_url($page_url);
    $page_request = parse_url( $page_url );

    $admin_bar->add_menu( array(
        'id'    => 'wp-hooks-finder',
        'title' => __('WP Hooks Finder', 'wphf_domain'),
        'href'  => '#',
        'meta'  => array(
            'title' => __('WP Hooks Finder', 'wphf_domain'),      
        ),
    ));
    $admin_bar->add_menu( array(
        'id'    => 'enable-disable-hooks',
        'parent' => 'wp-hooks-finder',
        'title' => wphf_is_active( 'wphf', 'All Action & Filter ' ),
        'href'  => wphf_is_url( $page_url, $page_request, 'wphf' ),
        'meta'  => array(
            'title' => wphf_is_active( 'wphf', 'All Action & Filter ' ),
            // 'target' => '_blank',
            'class' => 'wphf-menu'
        ),
    ));
    $admin_bar->add_menu( array(
        'id'    => 'enable-disable-action-hooks',
        'parent' => 'wp-hooks-finder',
        'title' => wphf_is_active( 'wphfa', 'Action' ),
        'href'  => wphf_is_url( $page_url, $page_request, 'wphfa' ),
        'meta'  => array(
            'title' => wphf_is_active( 'wphfa', 'Action' ),
            // 'target' => '_blank',
            'class' => 'wphf-menu'
        ),
    ));
    $admin_bar->add_menu( array(
        'id'    => 'enable-disable-filter-hooks',
        'parent' => 'wp-hooks-finder',
        'title' => wphf_is_active( 'wphff', 'Filter' ),
        'href'  => wphf_is_url( $page_url, $page_request, 'wphff' ),
        'meta'  => array(
            'title' => wphf_is_active( 'wphff', 'Filter' ),
            // 'target' => '_blank',
            'class' => 'wphf-menu'
        ),
    ));
}

/**
 * Return the URL depending on the request
 */
function wphf_is_url( $page_url, $page_request, $id ) {

    if( isset( $_GET[$id] ) && $_GET[$id] == 1 ) {
        $link = $page_url . ( isset( $page_request['query'] ) ? '&' : '?' ) . $id . '=0';
    } else {
        $link = $page_url . ( isset( $page_request['query'] ) ? '&' : '?' ) . $id . '=1';
    }

    return $link;
}

/**
 * Return what title should be render on menu
 */
function wphf_is_active( $id, $title ) {

    if( isset( $_GET[$id] ) && $_GET[$id] == 1 ) {
        return sprintf( __( 'Hide %s Hooks', 'wphf_domain' ), $title );
    } else {
        return sprintf( __( 'Show %s Hooks', 'wphf_domain' ), $title );
    }
}

/**
 * Reset the URL
 */
function wphf_clean_url( $url ) {

    $query_url = array( '?wphf=1', '?wphf=0', '&wphf=0', '&wphf=1', '?wphfa=1', '?wphfa=0', '&wphfa=0', '&wphfa=1', '?wphff=1', '?wphff=0', '&wphff=0', '&wphff=1' );

    foreach( $query_url as $q_url ) {
        if( strpos(  $url, $q_url ) !== false ) {
            $clean_url = str_replace( $q_url, '',$url );
            return $clean_url;            
        }
    }

    return $url;
}

/**
 * WordPress action hook "all", which is responsible to display hooks & filters
 * 
 * @since 1.0
 * @version 1.0
 */
add_action( 'all', 'wphf_display_all_hooks' );

function wphf_display_all_hooks( $tag ) {

    if( ( !isset( $_GET['wphf'] ) || $_GET['wphf'] == 0 ) &&
    ( !isset( $_GET['wphfa'] ) || $_GET['wphfa'] == 0 ) &&
    ( !isset( $_GET['wphff'] ) || $_GET['wphff'] == 0 ) ) return;

    global $debug_tags; global $wp_actions;
    
    if( !isset( $debug_tags ) )
        $debug_tags = array();

    if ( in_array( $tag, $debug_tags ) ) {
        return;
    }

    if( isset( $wp_actions[$tag] ) && ( isset( $_GET['wphf'] ) || isset( $_GET['wphfa'] ) ) ) {
        echo "<div id='wphf-action' title=' Action Hook'><img src='".WPHF_PLUGIN_PATH."assets/img/action.png' />" . '<a href="https://www.google.com/search?q='.$tag.'&btnI" target="_blank">'.$tag.'</a>' . "</div>";
    } else if( isset( $_GET['wphf'] ) || isset( $_GET['wphff'] ) ) {
        echo "<div id='wphf-filter' title='Filter Hook'><img src='".WPHF_PLUGIN_PATH."assets/img/filter.png' />" . '<a href="https://www.google.com/search?q='.$tag.'&btnI" target="_blank">' . $tag . '</a>' . "</div>";
    }

    $debug_tags[] = $tag;
}

<?php


function custom_search_form($form) {
    $form = '<form role="search" method="get" class="search-form position-relative" style="" action="' . esc_url(home_url('/')) . '">
    <label style="margin-top:0px;">
        <span class="screen-reader-text">' . __('Search for:', 'textdomain') . '</span>
        <input type="search" style="padding:15px;border-radius:4px;width:100%;min-width: 250px;" id="s" class="search-field" placeholder="' . esc_attr__('Search for a product here...', 'textdomain') . '" value="' . get_search_query() . '" name="s" />
    </label>
    <button type="submit" class="search-submit bg-accent-secondary d-flex justify-content-center align-items-center position-absolute" style="background:var(--accent-secondary);width:35px;top:5px;right:1%;border-radius:4px;">';
	$form .= '<div class="" style="width:20px;">';
	$form .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Free 6.2.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2022 Fonticons, Inc. --><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352c79.5 0 144-64.5 144-144s-64.5-144-144-144S64 128.5 64 208s64.5 144 144 144z"/></svg>';
	$form .= '</div>';

    $form .= '</button>';
    $form .= '</form>';
    return $form;
}
add_filter('get_search_form', 'custom_search_form');

?>
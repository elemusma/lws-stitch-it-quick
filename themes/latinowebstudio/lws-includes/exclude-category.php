<?php

add_action('pre_get_posts', 'exclude_category_from_shop_page');

  function exclude_category_from_shop_page($query) {
      if (is_shop() && $query->is_main_query() && !is_admin()) {
          // Replace 'exclude-category-slug' with the slug of the category you want to exclude
          $tax_query = (array) $query->get('tax_query');
          $tax_query[] = array(
              'taxonomy' => 'product_cat',
              'field' => 'slug',
              'terms' => array('gates'), // Change this to your category slug
              'operator' => 'NOT IN'
          );
  
          $query->set('tax_query', $tax_query);
      }
  }

?>
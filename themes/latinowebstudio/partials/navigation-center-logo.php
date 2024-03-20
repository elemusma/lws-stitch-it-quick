<header class="position-relative">

<div class="nav pt-3 pb-3 position-relative" style="box-shadow: 1px 1px 10px black;">

<div class="nav-toggler-col">
<a id="navToggle" class="nav-toggle">
        <div>
            <div class="line-1"></div>
            <div class="line-2"></div>
            <div class="line-3"></div>
        </div>
    </a>
    </div>


<div class="container">
    <div class="row align-items-center justify-content-center">
    <div class="col-md-4">
        <div class="d-flex justify-content-around align-items-center">
        <?php echo get_template_part('partials/si'); ?>
        <div class="mobile-hidden">
            <?php wp_nav_menu(array(
                'menu' => 'Left Menu',
                'menu_class'=>'menu d-flex flex-wrap list-unstyled justify-content-center mb-0'
            )); ?>
            </div>
            </div>
    </div>
    <div class="col-md-3 text-center col-10">
        <a href="<?php echo home_url(); ?>">
        <?php $logo = get_field('logo','options'); ?>
        <?php echo wp_get_attachment_image($logo['id'],'full',"",['class'=>'w-75 h-auto logo']); ?>
    </a>
    
    </div>
    <div class="col-md-4">
        <div class="mobile-hidden">
    <?php wp_nav_menu(array(
    'menu' => 'Right Menu',
    'menu_class'=>'menu d-flex flex-wrap list-unstyled justify-content-center mb-0'
    )); ?>
    </div>
    </div>
    <div class="col-12 nav-items desktop-hidden" id="navItems">
    <div class="pt-2 pb-2 pl-4 pr-4" id="mobileMenuHeight">
    <?php wp_nav_menu(array(
    'menu' => 'Left Menu',
    'menu_class'=>'menu list-unstyled mb-0'
    )); ?>
    <?php wp_nav_menu(array(
    'menu' => 'Right Menu',
    'menu_class'=>'menu list-unstyled mb-0'
    )); ?>
    </div>
    </div>
    </div>
    </div>
    </div>
</header>
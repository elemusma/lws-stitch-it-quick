<div class="container-fluid">
	<div >
		<nav >
			<a class="fme_current_tab fme_pgisfw_nav_tab" aria-controls="tab_default_1" > <?php echo esc_html__('General Settings', 'fme_pgisfw'); ?></a> |
			<a class="fme_pgisfw_nav_tab" aria-controls="tab_default_2" ><?php echo esc_html__('Thumbnail Settings', 'fme_pgisfw'); ?></a> |
			<a class="fme_pgisfw_nav_tab" aria-controls="tab_default_3" ><?php echo esc_html__('Bullets Settings', 'fme_pgisfw'); ?></a> |
			<a class="fme_pgisfw_nav_tab" aria-controls="tab_default_4" ><?php echo esc_html__('Arrows Settings', 'fme_pgisfw'); ?></a> |
			<a class="fme_pgisfw_nav_tab" aria-controls="tab_default_5" ><?php echo esc_html__('Lightbox Settings', 'fme_pgisfw'); ?> </a> |
			<a class="fme_pgisfw_nav_tab" aria-controls="tab_default_6" ><?php echo esc_html__('Zoom Settings', 'fme_pgisfw'); ?></a>	|
			<a class="fme_pgisfw_nav_tab" aria-controls="tab_default_7" ><?php echo esc_html__('Rule Based Settings', 'fme_pgisfw'); ?></a>
		</nav>
		<div class="">
			<div class="fme_pgisfw_main" id="tab_default_1">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/general-settings.php' ; ?>
				</div>
			</div>
			<div class="fme_pgisfw_main" id="tab_default_2">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/thumbnail-settings.php' ; ?>
				</div>
			</div>
			<div class="fme_pgisfw_main" id="tab_default_3">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/bullets_settings.php' ; ?>
				</div>
			</div>
			<div class="fme_pgisfw_main" id="tab_default_4">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/arrows-setting.php' ; ?>
				</div>
			</div>
			<div class="fme_pgisfw_main" id="tab_default_5">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/lightbox-settings.php' ; ?>
				</div>
			</div>
			<div class="fme_pgisfw_main" id="tab_default_6">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/zoom-settings.php' ; ?>
				</div>
			</div>
			<div class="fme_pgisfw_main" id="tab_default_7">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/rule-settings.php' ; ?>
				</div>
			</div>

		</div>
	</div>
</div>

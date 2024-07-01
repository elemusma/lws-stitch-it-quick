<?php 
	
if (isset($_GET['rule_id'])) {
	// $save_rule='hidden';
	// $edit_rule='button';
	$button_value=__('Update Rule', 'fme_pgisfw');
	$rule_id=filter_var($_GET['rule_id']);
	$heading=__('Edit Rule', 'fme_pgisfw');
} else {
	// $edit_rule='hidden';
	// $save_rule='button';
	$rule_id='';
	$button_value=__('Save Rule', 'fme_pgisfw');
	$heading=__('Add New Rule', 'fme_pgisfw');
}
?>

<div class="container-fluid">
<br class="clear">
<h3 style="font-weight:400; font-size: 21px;">	
	<?php echo esc_html__($heading); ?>
</h3>
</div>
<div class="container-fluid">
	<div >
		<nav >
			<a class="fme_current_tab fme_pgisfw_nav_tab" aria-controls="tab_default_8" > <?php echo esc_html__('General Settings', 'fme_pgisfw'); ?></a> |
			<a class="fme_pgisfw_nav_tab" aria-controls="tab_default_9" ><?php echo esc_html__('Thumbnail Settings', 'fme_pgisfw'); ?></a> |
			<a class="fme_pgisfw_nav_tab" aria-controls="tab_default_10" ><?php echo esc_html__('Bullets Settings', 'fme_pgisfw'); ?></a> |
			<a class="fme_pgisfw_nav_tab" aria-controls="tab_default_11" ><?php echo esc_html__('Arrows Settings', 'fme_pgisfw'); ?></a> |
			<a class="fme_pgisfw_nav_tab" aria-controls="tab_default_12" ><?php echo esc_html__('Lightbox Settings', 'fme_pgisfw'); ?> </a> |
			<a class="fme_pgisfw_nav_tab" aria-controls="tab_default_13" ><?php echo esc_html__('Zoom Settings', 'fme_pgisfw'); ?></a>	
			
		</nav>
		<div class="">
			<div class="fme_pgisfw_main" id="tab_default_8">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/add-new-rule-gs.php' ; ?>
				</div>
			</div>
			<div class="fme_pgisfw_main" id="tab_default_9">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/thumbnail-settings.php' ; ?>
				</div>
			</div>
			<div class="fme_pgisfw_main" id="tab_default_10">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/bullets_settings.php' ; ?>
				</div>
			</div>
			<div class="fme_pgisfw_main" id="tab_default_11">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/arrows-setting.php' ; ?>
				</div>
			</div>
			<div class="fme_pgisfw_main" id="tab_default_12">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/lightbox-settings.php' ; ?>
				</div>
			</div>
			<div class="fme_pgisfw_main" id="tab_default_13">
				<div >
					<?php require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/zoom-settings.php' ; ?>
				</div>
			</div>
			<div>
				<table>
					<tr>
						<td style="display: flow-root;">
							<input id="fme_pgisfw_update_rule_btn" type="button"  rule_id="<?php echo esc_html__($rule_id); ?>" name="fme_pgisfw_update_rule_btn" onclick="save_new_rule_settings();" class="button-primary " value="<?php echo esc_attr($button_value); ?>">
						</td>
						<td id="fme_pgisfw_td">
							<span id="fme_general_settings_msg" class="fme_success_alert">
								<b><?php esc_html_e('Success!', 'fme_pgisfw'); ?></b> <?php echo esc_html__(' Settings Saved Successfully!', 'fme_pgisfw'); ?>
							</span>
						</td>
					</tr>
				</table>
			</div>

		</div>
	</div>
</div>


<div class="panel panel-info" id="fme_settings_panel">
	<div class="panel-heading">
		<h2 class="panel-title" id="fme_panel_title"><?php echo esc_html__('Rule Based Settings', 'fme_pgisfw'); ?></h2>
	</div>
	<div class="panel-body">
		<table style="width: 100%;">
			<tr>
				<td style="vertical-align:bottom;float: right;">
					<button class="button" href="admin.php?page=wc-settings&tab=fme_pgisfw_add_new_rule" id="add_new_rule_pgisfw"><?php esc_html_e( 'Add Rule', 'fme_pgisfw' ); ?></button>
					<button class="button" id="delete_all_rules_inbulk"><?php esc_html_e( 'Delete All Rules', 'fme_pgisfw' ); ?></button>

				</td>
			</tr>
		</table>

				
	<form  id="accordion_from" type="post">				
				<?php
				$all_rules=get_option('fme_pgisfw_save_rule_settings');
				if (empty($all_rules)) {
					?>
					<h3>
						<?php esc_html_e( 'No rules are added.', 'fme_pgisfw' ); ?>
					</h3>
					<?php
					return;
				}



				//all_zones=all_rules zone=rule
				foreach ($all_rules as $key => $rule) {
					$rule_id=$key;
					$rule_name = $rule['fme_pgisfw_rule_name'];
					$rule_cateogry=$rule['fmeproductcategory'];
					$rule_priority=$rule['fme_pgisfw_rule_priority'];
					
					if ('fme_pgisfw_product'==$rule_cateogry) {
						$rule_cateogry='Specific Products';
					} else if ('fme_pgisfw_category'==$rule_cateogry) {
						$rule_cateogry='Specific Categories';
					} else {
						$rule_cateogry='All Products';
					}
					$rule_status=$rule['fme_pgisfw_enable_disable'];


					// $default_src=plugins_url( '../assets/images/camera_icon.png', __FILE__ );
					// if (!empty($shipping_method)) {
					//  $methods=true;
					?>
					<br>
					<button class="accordion" action="#"><?php echo esc_attr(sanitize_text_field($rule_name)); ?> </button>
					<div class="panel fme_pgisfw_acc_panel">
						<table class="accordion_table" data-default_img="<?php echo ( esc_html( $default_src ) ); ?>
						">
						<tr>
							<th><?php echo esc_html__('Rule ID', 'fme_pgisfw'); ?></th>
							<th><?php echo esc_html__('Rule Name', 'fme_pgisfw'); ?></th>
							<th><?php echo esc_html__('Rule Priority', 'fme_pgisfw'); ?></th>
							<th><?php echo esc_html__('Rule Category', 'fme_pgisfw'); ?></th>
							<th><?php echo esc_html__('Rule Status', 'fme_pgisfw'); ?></th>
						</tr>

							<tr class="accordion_row">
								<td>
								<?php
								echo esc_attr(intval($rule_id));
								?>
									
								</td>

								<td >
								<?php
								echo esc_attr(sanitize_text_field($rule_name));
								?>
									
								</td>

								<td  >
								<input style="width: 60px" class="fme_quick_rule_priority" type="number" name="" value="<?php echo esc_attr(intval(empty($rule_priority)?'1':$rule_priority )); ?>" min="1">
				 
								</td>

								<td  >
								<?php
								echo filter_var($rule_cateogry);
								?>
												 
								</td>
								<td  >
								<?php
								// echo filter_var($$rule_cateogry);
								?>
								<select  class="regular fme_quick_enable_disable" name="fme_quick_enable_disable" id="fme_quick_enable_disable">
									<option class="btn-success" <?php selected('yes', ( isset($rule['fme_pgisfw_enable_disable']) ) ? $rule['fme_pgisfw_enable_disable'] : '' ); ?> value="yes"><?php echo esc_html__('Enabled', 'fme_pgisfw'); ?>			
									</option>
									<option class="btn btn-danger" <?php selected('no', ( isset($rule['fme_pgisfw_enable_disable']) ) ? $rule['fme_pgisfw_enable_disable'] : '' ); ?> value="no"><?php echo esc_html__('Disabled', 'fme_pgisfw'); ?>			
								</option>
								</select>	             
								</td>
								<td>
									<input  rule_id="<?php echo esc_attr(intval($rule_id)); ?>" type="button" class="quick_rule_save_btn button-primary" name="Save" value="<?php echo esc_html__('Save', 'fme_pgisfw'); ?>" >

									<a class="button" id="edit_rule_pgisfw" href="<?php echo esc_url(admin_url('admin.php?page=wc-settings&tab=fme_pgisfw_add_new_rule&rule_id=' . esc_attr(intval($rule_id)) . '')); ?>"><?php echo esc_html__('Edit', 'fme_pgisfw'); ?> </a>
									<input rule_id="<?php echo esc_attr(intval($rule_id)); ?>" type="button" class="button-primary rule_delete " style="background: #d9534f !important; border:#d9534f;" name="Delete" value="<?php echo esc_html__('Delete', 'fme_pgisfw'); ?>">

								</td>
							</tr>
							</table>
							</div> 
							<?php 
							/*****For table each end***/

				}
				?>
					
				
				<br>
					
		
				<div class="save_and_notice">
				<br><br><br>
				<b><?php echo esc_html__('Note:', 'fme_pgisfw'); ?>	</b>
			<?php echo esc_html__('Rule with greater priority number has more priority.', 'fme_pgisfw'); ?>
			<br>

			 </div>

		</form>
		<div>
		<td>	
		<span id="fme_quick_success_alert" class="fme_quick_success_alert">
			<b><?php esc_html_e('Success!', 'fme_pgisfw'); ?></b> <?php echo esc_html__(' Settings Saved Successfully!', 'fme_pgisfw'); ?>
		</span>
		</td>
		</div>

				</div> 
				
			</div>
		</div>

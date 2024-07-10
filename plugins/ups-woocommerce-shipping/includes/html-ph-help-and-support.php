<style>

	.ph_ups_important_links {
		margin-left: 45px !important;
		list-style-type: square !important;
	}

	.ph_ups_important_links li {
		margin-bottom: 10px !important;
	}

	.ph_ups_submit_ticket {
		background: #1e3368 !important;
		border-color: #1e3368 !important;
		color: white !important;
	}

	.ph_ups_submit_ticket:hover {
		background: #15254f !important;
		border-color: #15254f !important;
		color: white !important;
	}

	.required_field {
		border: 1px solid red !important;
	}

	.ph_ups_help_table {
		margin-top: 15px !important;
	}
	.ph_ups_help_table tr td {
		padding: 0px !important;
	}

	.ph_ups_ticket_number_error {

		color: red;
		font-size: small;
		padding: 10px !important;
		vertical-align: sub;
		display: none;
	}

	.ph_ups_consent_error {
		color: red;
		font-size: small;
		vertical-align: sub;
		display: none;
	}
</style>

<tr valign="top" class="ph_ups_help_tab">
	<td class="titledesc" colspan="2" style="padding:0px">

		<h3>Important Links</h3>

		<ul class="ph_ups_important_links">
			<li>
				How to Set Up WooCommerce UPS Shipping Plugin?&nbsp;&nbsp;&nbsp;
				<a href="https://www.pluginhive.com/knowledge-base/setting-woocommerce-ups-shipping-plugin/" target="_blank"><b>Read More</b></a>
			</li>
			<li>
				How WooCommerce UPS Shipping plugin works?&nbsp;&nbsp;&nbsp;
				<a href="https://www.pluginhive.com/knowledge-base/woocommerce-ups-shipping-plugin-works/" target="_blank"><b>Read More</b></a>
			</li>
			<li>
				How to Troubleshoot WooCommerce UPS Shipping plugin?&nbsp;&nbsp;&nbsp;
				<a href="https://www.pluginhive.com/knowledge-base/troubleshooting-woocommerce-ups-plugin/" target="_blank"><b>Read More</b></a>
			</li>
			<li>
				How to Troubleshoot UPS Shipping Rates Mismatch?&nbsp;&nbsp;&nbsp;
				<a href="https://www.pluginhive.com/knowledge-base/troubleshoot-higher-rates-ups-shipping-plugin/" target="_blank"><b>Read More</b></a>
			</li>
			<li>
				How to Automatically Print UPS Shipping Labels?&nbsp;&nbsp;&nbsp;
				<a href="https://www.pluginhive.com/knowledge-base/automatic-print-shipping-label-woocommerce-ups-plugin/" target="_blank"><b>Read More</b></a>
			</li>
			<li>
				How to Send UPS Tracking Details to Customers?&nbsp;&nbsp;&nbsp;
				<a href="https://www.pluginhive.com/live-ups-shipment-tracking-woocommerce-store/" target="_blank"><b>Read More</b></a>
			</li>
		</ul>

		<hr/>

		<h3>Video Tutorials</h3>

		<table style="width:100%;">
			<tr>
				<td style="text-align: center;">
					<h2>"No Shipping Method Available"</h2>
					<center>
						<div class="video_content">
							<iframe width="250" height="150" src="https://www.youtube.com/embed/GfNkqLykFto?autoplay=0" frameborder="0" allowfullscreen></iframe>
						</div>
					</center>
				</td>
				<td style="text-align: center;">
					<h2>"Not Displaying All UPS Services"</h2>
					<div class="video_content">
						<iframe width="250" height="150" src="https://www.youtube.com/embed/9m8FMwo_amo?autoplay=0" frameborder="0" allowfullscreen></iframe>
					</div>
				</td>
				<td style="text-align: center;">
					<h2>"Getting Inaccurate Shipping Rates"</h2>
					<center>
						<div class="video_content">
							<iframe width="250" height="150" src="https://www.youtube.com/embed/aUuZUUt6ac4?autoplay=0" frameborder="0" allowfullscreen></iframe>
						</div>
					</center>
				</td>
				<td style="text-align: center;">
					<h2>"Automatic UPS Label Printing"</h2>
					<div class="video_content">
						<iframe width="250" height="150" src="https://www.youtube.com/embed/h3CR_k3ypw8?autoplay=0" frameborder="0" allowfullscreen></iframe>
					</div>
				</td>
			</tr>
			
		</table>

		<hr/>

		<h3>Submit Your Query</h3>

		<p>Click the button to visit the PluginHive Support page and submit your query. The support team will get back to you within 1 business day.</p>
		<br/>
		<a class="button ph_ups_submit_ticket" href="https://www.pluginhive.com/support/" target="_blank">Contact Us</a>
		<br/><br/>
		<hr/>

		<h3>Submit a Diagnostic Report</h3>

		<p>1. Please enusre that the Debug Mode is enabled</p>
		<p>2. After enabling Debug Mode, please try recreating your issue(s)</p>
		<p>3. Submit Diagnostic Report only when asked by the PluginHive Support Team</p>
		<p>4. Clicking on Send button will send Debug Log Details and Plugin Settings to PluginHive Support Team automatically</p>
		<p>5. The details sent to PluginHive will include UPS Account Details for debugging purposes only</p>

		<table class="ph_ups_help_table">

			<tr>
				<td colspan="2">
					<input type="checkbox" name="ph_ups_consent" id="ph_ups_consent">
					Yes, I have read the above points and agreed to send the details mentioned above for debugging purposes.
					<br/>
					<span class="ph_ups_consent_error">Please read the instructions & agree to proceed by selecting the checkbox</span>
				</td>
			</tr>

			<tr>
				<th>Reference Ticket Number</th>

				<td>
					<input type="text" name="ph_ups_ticket_number" id="ph_ups_ticket_number">
					<span class="ph_ups_ticket_number_error">Please enter a valid reference ticket number.</span>
				</td>
			</tr>
			
			<tr>
				<td colspan="2">
					<p>Please enter the correct reference ticket number. The information sent with an Incorrect or Invalid ticket number will be discarded.</p>
				</td>
			</tr>

		</table>

		<br/>

		<input type="button" id="ph_ups_submit_ticket" class="button ph_ups_submit_ticket" value="Send Report">

	</td>
</tr>
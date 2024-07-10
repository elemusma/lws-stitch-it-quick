<style>
    .ph-ups-freight-banner-section{
        margin: 5px 0px;
        z-index:1;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: white;
        padding: 10px;
    }

    #ph_ups_freight_close_button {
        float:right;
        display:inline-block;
        padding:5px 5px;
        background:#ccc;
        box-shadow: none;
        cursor: pointer;
        border: 1px solid #ccc;
        z-index: 1;
        border-radius: 5px;
    }
</style>

<script type="text/javascript">
    
    jQuery(document).ready(function(c) {
        jQuery('#ph_ups_freight_close_button').on('click', function(c){
            jQuery('.ph-ups-freight-banner-section').fadeOut('slow', function(c){
                jQuery('.ph-ups-freight-banner-section').remove();
            });
        }); 
    });

</script>
<div class="ph-ups-freight-banner-section">

    <div id='ph_ups_freight_close_button'>[X]</div>

    <strong><p style="margin: 0px; font-size: 15px"><?php echo __("TForce Freight Update!", "ups-woocommerce-shipping") ?></p></strong><br>
    
    <?php echo __("As of May 1st, 2024, TForce Freight shipping is no longer supported through UPS. Being a UPS Ready plugin, this plugin will support the changes and will remove the support for TForce Freight services in the upcoming versions.<br>To know more about the plugin changes, reach out to us at support@pluginhive.com.","ups-woocommerce-shipping") ?>

    <br><br><?php echo __("But don't worry!", "ups-woocommerce-shipping") ?>
    
    <br><?php echo __("PluginHive Team is building a dedicated TForce Freight plugin for WooCommerce to keep your shipments flowing smoothly.","ups-woocommerce-shipping") ?>
    <br><?php echo __("Stay tuned for its release soon! ","ups-woocommerce-shipping") ?>&#128640;
</div>
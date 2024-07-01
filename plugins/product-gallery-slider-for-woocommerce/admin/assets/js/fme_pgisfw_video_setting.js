jQuery(function () {
	var counter = jQuery('#fme_count_url_val').val();
	if(''==counter) {
		var count = 1;
	} else {
		var count = parseInt(counter) ;
	}
	jQuery("#fme_pgisfw_btnAdd").bind("click", function () {
		
		count++;
		var div = jQuery("<tr/>");
		div.html(GetDynamicTextBox("",count));
		jQuery("#TextBoxContainer").append(div);
		var ii=1;
			jQuery('.fme_pdifw_allurls').each(function(){
				jQuery(this).attr('id','url'+ii);
				jQuery(this).parent().next().find('button').attr('id','idis'+ii)
				ii++;
			});
		
		
	});
	jQuery("body").on("click", ".fme_pgifw_deletebtn", function () {
		jQuery(this).closest("tr").remove();
		count--;
			var ii=1;
			jQuery('.fme_pdifw_allurls').each(function(){
				jQuery(this).attr('id','url'+ii);
				ii++;
			});
	});
});
function GetDynamicTextBox(value , count) {
	return '<td><input name ="url[]" class="fme_pdifw_allurls" required type="url"  placeholder="https://example.com" style="width: 100%;" value = "' + value + '" class="form-control" /></td>' + '<td style="width:4%;"><button class="fme_pgisfw_upload_btn" type="button" id="uploadit'+count+'" onclick="fme_upload_video('+count+');">'+fme_pgisfw_strings.choose+'</button></td>' + '<td><button type="button" id="deletebtn' + count + '" class="fme_pgifw_deletebtn" >'+fme_pgisfw_strings.delete+'</button></td> ';
}

function fme_upload_video(count) {
	var file = wp.media({ 
		title: 'Upload File/Image',
		multiple: false
	}).open()
	.on('select', function(e){
		var uploaded_image = file.state().get('selection').first();
		uploaded_image_url = uploaded_image.toJSON().url;
		var type = uploaded_image.toJSON().type;

		if(type=='video') {
			jQuery('#url'+count).val(uploaded_image_url);
		} else {
			alert('Please select valid File only video Supported');
		}
	});
        
}
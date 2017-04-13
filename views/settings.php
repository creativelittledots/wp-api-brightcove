<form method="post">
	
	<div>

		<input type="text" class="widefat" name="brightcove_api[upload_key]" value="<?php echo $settings->upload_key; ?>" />
		
	</div>
	
	<div>
	
		<input type="text" class="widefat" name="brightcove_api[meta_key]" value="<?php echo $settings->meta_key; ?>" />
		
	</div>
	
	<div>
	
		<input type="text" class="widefat" name="brightcove_api[account_id]" value="<?php echo $settings->account_id; ?>" />
		
	</div>
	
	<div>
	
		<input type="text" class="widefat" name="brightcove_api[client_id]" value="<?php echo $settings->client_id; ?>" />
		
	</div>
	
	<div>
	
		<input type="text" class="widefat" name="brightcove_api[client_secret]" value="<?php echo $settings->client_secret; ?>" />
		
	</div>
	
	<div>
	
		<input type="text" class="widefat" name="brightcove_api[allowed_extentions]" value="<?php echo implode( ', ', $settings->allowed_extentions ); ?>" />
		
	</div>
	
	<div>
		
		<input name="save" type="submit" class="button button-primary button-large" value="Save Settings">
		
	</div>
	
</form>
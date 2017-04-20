<form method="post">
	
	<h2>Brightcove API Settings</h2>
	
	<table class="form-table">
	
		<tbody>
			
			<tr>
			
				<th>
					
					<label for="category_base">Upload Key</label>
					
				</th>
				
				<td> 
					
					<input type="text" class="regular-text code" name="brightcove_api[upload_key]" value="<?php echo $settings->upload_key; ?>" />
					
					<p class="description">The param name sent through the api.</p>
					
				</td>
			
			</tr>
			
			<tr>
		
				<th>
					
					<label for="tag_base">Meta Key</label>
					
				</th>
				
				<td> 
					
					<input type="text" class="regular-text code" name="brightcove_api[meta_key]" value="<?php echo $settings->meta_key; ?>" />
					
					<p class="description">The meta key saved to the object and returned in the api.</p>
					
				</td>
				
			</tr>
			
			<tr>
			
				<th>
					
					<label for="category_base">Account ID</label>
					
				</th>
				
				<td> 
					
					<input type="text" class="regular-text code" name="brightcove_api[account_id]" value="<?php echo $settings->account_id; ?>" />
					
				</td>
			
			</tr>
			
			<tr>
			
				<th>
					
					<label for="category_base">Client ID</label>
					
				</th>
				
				<td> 
					
					<input type="text" class="regular-text code" name="brightcove_api[client_id]" value="<?php echo $settings->client_id; ?>" />
					
				</td>
			
			</tr>
			
			<tr>
			
				<th>
					
					<label for="category_base">Client Secret</label>
					
				</th>
				
				<td> 
					
					<input type="text" class="regular-text code" name="brightcove_api[client_secret]" value="<?php echo $settings->client_secret; ?>" />
					
				</td>
			
			</tr>
			
			<tr>
			
				<th>
					
					<label for="category_base">Allowed Mime Types</label>
					
				</th>
				
				<td> 
					
					<input type="text" class="regular-text code" name="brightcove_api[allowed_extensions]" value="<?php echo implode( ', ', $settings->allowed_extensions ); ?>" />
										
				</td>
			
			</tr>
		
		</tbody>
		
	</table>
	
	<p>
		
		<input name="save" type="submit" class="button button-primary button-large" value="Save Settings">
		
	</p>
	
</form>
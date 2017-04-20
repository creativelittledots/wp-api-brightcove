WP API Brightcove

A Wordpress Plugin that integrates with WP Rest API 'Create Post' endpoint to push in video file uploads into Brightcove Dynamic Ingest API Currently working on plugin page to manage variables within the Plugin.

## How to Use

Simply enter your credentials in the settings page found at /wp-admin/options-general.php?page=brightcove-api

Then make a request to your api:

```js

	var form = new FormData();
	form.append("content", "Lorem Ipsum");
	form.append("title", "Some Title");
	form.append("status", "publish");
	form.append("brightcove_video_upload", "blank.m4v");
	
	// 'brightcove_video_upload' is our uplod key as per settings
	
	var key = 'some-api-token-key-if-you-are-using-auth';
	settings = {
	  "async": true,
	  "crossDomain": true,
	  "url": "http://domain.com/api/wp-json/wp/v2/posts",
	  "method": "POST",
	  "headers": {
	    "authorization": "Bearer " + key,
	    "cache-control": "no-cache",
	    "postman-token": "bd312945-02aa-4434-b80b-d99556e4b135"
	  },
	  "processData": false,
	  "contentType": false,
	  "mimeType": "multipart/form-data",
	  "data": form
	}
	
	$.ajax(settings).done(function (response) {
	  console.log(response);
	});

``` 


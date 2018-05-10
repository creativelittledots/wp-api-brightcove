<?php
	
	/**
	 * Plugin Name: WP API BrightCove
	 * Plugin URI: https://github.com/creativelittledots/wp-api-brightcove
	 * Version: 1.0.0
	 * Description: A Wordpress Plugin that integrates with WP Rest API 'Create Post' endpoint to push in video file uploads into Brightcove Dynamic Ingest API
	 * Author: Creative Little Dots
	 *
	 * @author  Terence O'Donoghue <terence@creativelittledots.co.uk>
	 * @package WP API BightCove
	 */
	 
	namespace WP_API_BrightCove;
	 
	// Use composer to load the autoloader.
	require __DIR__ . '/vendor/autoload.php';
	
	class DI extends \Brightcove\API\DI {
		
		/**
		* @return IngestResponse
		*/
		public function uploadUrls($video_id, $source_name) {
			$source_name = urlencode($source_name);
			return $this->diRequest('GET', "/videos/{$video_id}/upload-urls/$source_name", null);
		}
		
	}
	
	class WP_API_BrightCove {
		
		private static $instance;
		private $upload_key = 'brightcove_video_upload';
		private $meta_key = 'brightcove_video';
		private $account_id = '';
		private $client_id = '';	
		private $client_secret = '';	
		private $allowed_extensions = array('video/webm', 'video/mp4', 'video/ogv');
	
		public static function init() {
			
			if( empty( static::$instance ) ) {
				
				static::$instance = new self();
				
			}
			
			return static::$instance;
			
		}
		
		public function __construct() {
			
			foreach ( get_post_types() as $post_type ) {
			
				add_action( 'rest_insert_' . $post_type, array($this, 'processRequest'), 10, 3);
				
			}
			
			add_action( 'gform_after_create_post', array( $this, 'uploadGformVideo'), 10, 3 );
			
			add_action( 'admin_menu', array($this, 'addSettingsPage' ) );
			
			add_filter( 'rest_prepare_post', array($this, 'outputVideoMeta'), 10, 3);
			
		}
		
		public function uploadGformVideo( $post_id, $entry, $form ) {
			
			$this->loadSettings();
			
			if( $video = get_post_meta( $post_id, $this->meta_key, true) ) {
				
				$this->processFile( $post_id, basename( $video ), $video );
				
			}
			
		}
		
		public function outputVideoMeta($response, $post) {
			
			if( $meta = get_post_meta( $post->ID, $this->meta_key, true ) ) {
			
				$response->data[$this->meta_key] = $meta;
				
			}
			
			return $response;
			
		}
		
		public function addSettingsPage() {
			
			add_options_page( 'BrightCove API', 'BrightCove API', 'read', 'brightcove-api', array($this, 'addSettingsPageView' ) );
			
		}
		
		public function loadSettings() {
			
			$settings = get_option( 'brightcove_api_settings' );
			
			$this->upload_key = ! empty( $settings['upload_key'] ) ? $settings['upload_key'] : $this->upload_key;
			$this->meta_key = ! empty( $settings['meta_key'] ) ? $settings['meta_key'] : $this->meta_key;
			$this->account_id = ! empty( $settings['account_id'] ) ? $settings['account_id'] : $this->account_id;
			$this->client_id = ! empty( $settings['client_id'] ) ? $settings['client_id'] : $this->client_id;
			$this->client_secret = ! empty( $settings['client_secret'] ) ? $settings['client_secret'] : $this->client_secret;
			$this->allowed_extensions = ! empty( $settings['allowed_extensions'] ) ? $settings['allowed_extensions'] : $this->allowed_extensions;
			
		}
		
		public function getSettings() {
			
			$this->loadSettings();
			
			return array(
				'upload_key' => $this->upload_key,
				'meta_key' => $this->meta_key,
				'account_id' => $this->account_id,
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
				'allowed_extensions' => $this->allowed_extensions,
			);
			
		}
		
		public function saveSettings() {
			
			if( ! empty( $_POST['brightcove_api'] ) ) {
				
				$brightcove_api_settings = $_POST['brightcove_api'];
				
				$brightcove_api_settings['allowed_extensions'] = array_map( 'trim', explode( ',', $brightcove_api_settings['allowed_extensions'] ) );
				
				update_option( 'brightcove_api_settings', $brightcove_api_settings );
				
			}
			
		}
		
		public function addSettingsPageView() {
			
			$this->saveSettings();
			
			ob_start();
			
			$settings = (object) $this->getSettings();
			
			include( 'views/settings.php' );
			
			$html = ob_get_contents();
			
			ob_end_clean();
			
			echo $html;
			
		}

		public function processRequest($post, $request, $create) {
			
			$this->loadSettings();
			
			if( ! empty( $_FILES[ $this->upload_key ] ) ) {
				
				$file = $_FILES[ $this->upload_key ];
				
				if( ! in_array( $file['type'], $this->allowed_extensions ) ) {
					
					$this->jsonError( 'rest_no_incorrect_format', __( "{$this->upload_key} is an incorrect file format for Brightcove. Allowed mime types include " . implode( ', ', $this->allowed_extensions ) ), 401 );
					
				}
				
				if( ! $this->client_id ) {
					
					$this->jsonError( 'rest_no_client_id', __( 'You must provide a client_id for Brightcove.' ), 401 );
					
				}
				
				if( ! $this->client_secret ) {
					
					$this->jsonError( 'rest_no_client_secret', __( 'You must provide a client_secret for Brightcove.' ), 401 );
					
				}
				
				if( ! $this->account_id ) {
					
					$this->jsonError( 'rest_no_account_id', __( 'You must provide a account_id for Brightcove.' ), 401 );
					
				}
				
				try {
					
					$this->processFile( $post->ID, basename( $file['name'] ), $file['tmp_name'] );
				    
				} catch (\Exception $e) {
					
					$this->jsonError( 'rest_aws_s3_upload_error', __( "There was an error uploading the file to AWS S3: " . $e->getMessage() ), 401 );
				    
				}
								
			}
			
		}
		
		public function processFile($post_id, $filename, $url) {
					
			$client = \Brightcove\API\Client::authorize($this->client_id, $this->client_secret);
			$cms = new \Brightcove\API\CMS($client, $this->account_id);
			$di = new DI($client, $this->account_id); 
			
			$video = new \Brightcove\Object\Video\Video();
			$video->setName($filename);
			$video = $cms->createVideo($video);
			
			update_post_meta( $post_id, $this->meta_key, $this->getUrl( $video->getId() ));
		
		    $response = (object) $di->uploadUrls($video->getId(), $filename);
		    
		    $credentials = array(
	            'key'    => $response->access_key_id,
	            'secret' => $response->secret_access_key,
	            'token'	 => $response->session_token
	        );
		    
		    $s3 = new \Aws\S3\S3Client([
			    'version' => 'latest',
			    'region'  => 'us-east-1',
			    'credentials' => $credentials
			]);
			
			$params = array(
		        'bucket' => $response->bucket,
		        'key' => $response->object_key
		    );
		    
		    $uploader = new \Aws\S3\MultipartUploader($s3, $url, $params);
			
			$uploadResponse = $uploader->upload();
		    
		    $request = \Brightcove\API\Request\IngestRequest::createRequest($response->api_request_url, 'high-resolution');
				
			$di->createIngest($video->getId(), $request);
					
		}
		
		/**
		 * Retrieves an appropriate error representation in JSON.
		 *
		 * Note: This should only be used in WP_REST_Server::serve_request(), as it
		 * cannot handle WP_Error internally. All callbacks and other internal methods
		 * should instead return a WP_Error with the data set to an array that includes
		 * a 'status' upload_key, with the value being the HTTP status to send.
		 *
		 * @since 4.4.0
		 * @access protected
		 *
		 * @param string $code    WP_Error-style code.
		 * @param string $message Human-readable message.
		 * @param int    $status  Optional. HTTP status code to send. Default null.
		 * @return string JSON representation of the error
		 */
		protected function jsonError( $code, $message, $status = null ) {
			if ( $status ) {
				$this->setStatus( $status );
			}
			$error = compact( 'code', 'message' );
			return wp_send_json( $error );
		}	
		
		/**
		 * Sends an HTTP status code.
		 *
		 * @since 4.4.0
		 * @access protected
		 *
		 * @param int $code HTTP status.
		 */
		protected function setStatus( $code ) {
			status_header( $code );
		}
		
		protected function getUrl( $id ) {
			
			return 'http://players.brightcove.net/' . $this->account_id . '/default_default/index.html?videoId=' . $id;
			
		}
	
	}
	
	add_action( 'init', 'WP_API_BrightCove\WP_API_BrightCove::init' );

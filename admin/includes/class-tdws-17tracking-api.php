<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tdwebservices.com
 * @since      1.1.0
 *
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/admin/includes
 */

class Tdws_Order_Tracking_System_17TrackAPI {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	protected $apiKey;

	protected $config;

	const TDWS_17TRACK_API_VERSION = '/v2.2';

	const TDWS_17TRACK_REGISTER_URI = '/register';

	const TDWS_17TRACK_CHANGE_CARRIER_URI = '/changecarrier';

	const TDWS_17TRACK_STOP_TRACK_URI = '/stoptrack';

	const TDWS_17TRACK_RE_TRACK_URI = '/retrack';

	const TDWS_17TRACK_DELETE_TRACK_URI = '/deletetrack';

	const TDWS_17TRACK_GET_TRACK_INFO_URI = '/gettrackinfo';
	
	const TDWS_17TRACK_GET_TRACK_LIST_URI = '/gettracklist';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.1.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$tdws_17track_opt = get_option( 'tdws_17track_opt' );
		$track17_change_key = isset($tdws_17track_opt['change_key']) ? $tdws_17track_opt['change_key'] : 0;
		$this->apiKey = $track17_change_key;
		$this->config = new TDWS_17TRACKING_Config( $this->apiKey, 'https://api.17track.net/track' );	

	}

	/**
	 * Register tracking no
	 *
	 * @since    1.1.0
	 */
	public function register( $trackNumber, $carrier = '', $tag = '', $extra_data = array() ) {
		
		$params = ['number' => $trackNumber];
		if(!empty($carrier)) {
			$params['carrier'] = $carrier;
		}
		if(!empty($tag)) {
			$params['tag'];
		}
		if( is_array($extra_data) && count($extra_data) > 0 ){			
			$params = array_merge( $params , $extra_data );
		}
		$response = $this->registerMulti([
			$params
		]);		
		$error_log = $this->checkErrors( $response, self::TDWS_17TRACK_REGISTER_URI );
		if( $error_log ){			
			return $error_log;
		}
		return true;

	}

	/**
	 * Get tracking info by no
	 *
	 * @since    1.1.0
	 */
	public function getTrackInfo( $trackNumber, $carrier = '' ) {
		
		$trackInfo = $this->getTrackInfoMulti([[
			'number' => $trackNumber,
			'carrier' => $carrier
		]]);
		$error_log = $this->checkErrors( $trackInfo, self::TDWS_17TRACK_GET_TRACK_INFO_URI );			
		if( $error_log ){
			return $error_log;
		}
		return isset($trackInfo['data']['accepted'][0]) ? $trackInfo['data']['accepted'][0] : array();

	}

	/**
	 * Get full tracking info by no
	 *
	 * @since    1.1.0
	 */
	public function getPureTrackInfo( $trackNumber, $carrier = '' ){		
		$trackInfo = $this->getTrackInfoMulti([[
			'number' => $trackNumber,
			'carrier' => $carrier
		]]);
		$error_log = $this->checkErrors( $trackInfo, self::TDWS_17TRACK_GET_TRACK_INFO_URI );			
		if( $error_log ){
			return $error_log;
		}
		$trackInfo = isset($trackInfo['data']['accepted'][0]) ? $trackInfo['data']['accepted'][0] : array();				
		return $trackInfo;
	}

	/**
	 * Get all tracking info by tracking arguments
	 *
	 * @since    1.1.0
	 */
	public function getAllTracklist( $args ){		
		$trackList = $this->getTrackList( $args );	
		$error_log = $this->checkErrors( $trackInfo, self::TDWS_17TRACK_GET_TRACK_LIST_URI );			
		if( $error_log ){
			return $error_log;
		}
		$trackList = isset($trackList['data']['accepted']) ? $trackList['data']['accepted'] : array();					
		return $trackList;
	}	


	/**
	 * Get all tracking events by tracking data
	 *
	 * @since    1.1.0
	 */
	public function getAllTrackEvents( $trackInfo = array(), $only_time = 0 ){
		$shipping_info = isset($trackInfo['track_info']['tracking']) ? $trackInfo['track_info']['tracking'] : array();				
		$main_event_stages = $main_event_time_stages = array();
		if( $shipping_info ){
			foreach ( $shipping_info as $key => $s_value ) {
				if( is_array($s_value) && count($s_value) > 0 ){
					foreach ( $s_value as $key => $p_value) {					
						if( is_array($p_value['events']) && count($p_value['events']) > 0 ){
							foreach ( $p_value['events'] as $key => $t_value ) {
								$main_stage_key = tdws_17track_find_tracking_status_sub_stage( $t_value['sub_status'] );
								if( $t_value['stage'] == 'OutForDelivery' ){
									$main_stage_key = 'out_for_delivery';	
								}
								if( $t_value['stage'] == 'PickedUp' ){
									$main_event_stages['pickup'][] = $t_value;
									$main_event_time_stages['pickup'][] = $t_value['time_utc'];
								}
								$main_event_stages[$main_stage_key][] = $t_value;
								$main_event_time_stages[$main_stage_key][] = $t_value['time_utc'];								
							}
							$first_event_time = end($p_value['events']);
							$main_event_stages['shipped'][] = $first_event_time;
							$main_event_time_stages['shipped'][] = $first_event_time['time_utc'];
						}
					}
				}
				
			}
		}		
		if( $only_time == 1 ){
			return $main_event_time_stages;
		}
		if( $only_time == 2 ){
			return [ 'event' => $main_event_stages, 'time' => $main_event_time_stages ];
		}
		return $main_event_stages;
	}

	/**
	 * Create tracking event message
	 *
	 * @since    1.1.0
	 */
	public function createTrackEvent( $date, $content, $location, $currentStatusCode = null ) { 

		$NOT_FOUND_CODE = 0;
		$IN_TRANSIT_CODE = 10;
		$ALERT_CODE = 20;
		$PICKUP_CODE = 30;
		$UNDELIVERED_CODE = 35;
		$DELIVERED_CODE = 40;
		$EXPIRED_CODE = 50;

		$STATUS_CODES = [
			$NOT_FOUND_CODE => 'Not found',
			$IN_TRANSIT_CODE => 'In transit',
			$ALERT_CODE => 'Alert',
			$PICKUP_CODE => 'Pick up',
			$UNDELIVERED_CODE => 'Undelivered',
			$DELIVERED_CODE => 'Delivered',
			$EXPIRED_CODE => 'Expired'
		];

    // Determine status name
		$currentStatusName = isset($STATUS_CODES[$currentStatusCode]) ? $STATUS_CODES[$currentStatusCode] : '';

    // Return the track event as an associative array
		return [
			'date' => $date,
			'content' => $content,
			'location' => $location,
			'currentStatusCode' => $currentStatusCode,
			'currentStatusName' => $currentStatusName
		];
	}


	/**
	 * Collect all tracking Event Movements
	 *
	 * @since    1.1.0
	 */
	public function collectTrackEvents( $mergedEvents, $commonTrackStatusCode ){
		
		$trackEvents = [];
		
		if( $mergedEvents ){
			foreach ($mergedEvents as $event) {
				$trackEvents[] = $this->createTrackEvent($event['a'], $event['z'], $event['c'] . ' ' . $event['d'], $commonTrackStatusCode );
			}	
		}

		return $trackEvents;
	}	

	/**
	 * Merge tracking Event Movements
	 *
	 * @since    1.1.0
	 */
	public function mergeCarriersEvents( $trackInfo ) {
		$mergedEvents = array_merge($trackInfo['track']['z1'], $trackInfo['track']['z2']);
		usort( $mergedEvents, function ( $itemOne, $itemSecond ) {
			return strtotime($itemSecond['a']) - strtotime($itemOne['a']);
		});
		return $mergedEvents;
	}

	/**
	 * Get last tracking Event
	 *
	 * @since    1.1.0
	 */
	public function getLastTrackEvent( $trackNumber, $carrier = '') {
		$trackInfo = $this->getTrackInfoMulti([[
			'number' => $trackNumber,
			'carrier' => $carrier
		]]);

		$this->checkErrors( $trackInfo, self::TDWS_17TRACK_GET_TRACK_INFO_URI );

		$trackInfo = isset($trackInfo['data']['accepted'][0]) ? $trackInfo['data']['accepted'][0] : array();

		$this->checkEventHistory($trackInfo);

		$lastEvent = $trackInfo['track']['z0'];

		return $this->createTrackEvent( $lastEvent['a'], $lastEvent['z'], $lastEvent['c'] . ' ' . $lastEvent['d'], $trackInfo['track']['e'] );
	}	

	/**
	 * Get last multi tracking Event
	 *
	 * @since    1.1.0
	 */
	public function getLastTrackEventMulti( $trackNumbers ) {
		$preparedTrackNumbers = [];
		
		if( $trackNumbers ){
			foreach ($trackNumbers as $trackNumber) {
				$preparedTrackNumbers[] = ['number' => $trackNumber];
			}	
		}		

		$tracksInfo = $this->getTrackInfoMulti( $preparedTrackNumbers );

		$this->checkErrors( $tracksInfo, self::TDWS_17TRACK_GET_TRACK_INFO_URI );

		$lastTracksEvents = [];
		if( isset($tracksInfo['data']['accepted'] ) && !empty($tracksInfo['data']['accepted'] ) ){
			foreach ($tracksInfo['data']['accepted'] as $trackInfo) {
				if (!empty($trackInfo['track']['z0'])) {
					$event = $trackInfo['track']['z0'];
					$lastTracksEvents[$trackInfo['number']] = $this->createTrackEvent(
						$event['a'], $event['z'],
						$event['c'] . ' ' . $event['d'],
						$trackInfo['track']['e']);
				}
			}
		}

		return $lastTracksEvents;
	}

	/**
	 * Change carrier api function
	 *
	 * @since    1.1.0
	 */
	public function changeCarrier( $trackNumber, $carrierNew, $carrierOld = '' ) {

		$response = $this->changeCarrierMulti([[
			'number' => $trackNumber,
			'carrier_old' => $carrierOld,
			'carrier_new' => $carrierNew			
		]]);

		$error_log = $this->checkErrors( $response, self::TDWS_17TRACK_CHANGE_CARRIER_URI );
		
		if( $error_log ){			
			return $error_log;
		}
		
		return true;
	}

	/**
	 * stop tracking api function
	 *
	 * @since    1.1.0
	 */
	public function stopTracking( $trackNumber, $carrier = '' ) {

		$response = $this->stopTrackingMulti([[
			'number' => $trackNumber,
			'carrier' => $carrier,
		]]);

		$this->checkErrors( $response, self::TDWS_17TRACK_STOP_TRACK_URI );

		return true;
	}

	/**
	 * re-tracking api function
	 *
	 * @since    1.1.0
	 */
	public function reTrack( $trackNumber, $carrier = null ) {
		
		$response = $this->reTrackMulti([[
			'number' => $trackNumber,
			'carrier' => $carrier,
		]]);

		$this->checkErrors( $response, self::TDWS_17TRACK_RE_TRACK_URI );

		return true;
	}

	/**
	 * delete-tracking api function
	 *
	 * @since    1.1.0
	 */
	public function deleteTrack( $trackNumber, $carrier = null ) {
		
		$response = $this->deleteTrackMulti([[
			'number' => $trackNumber,
			'carrier' => $carrier,
		]]);

		$this->checkErrors( $response, self::TDWS_17TRACK_DELETE_TRACK_URI );

		return true;
	}

	/**
	 * register multi tracking api function
	 *
	 * @since    1.1.0
	 */
	public function registerMulti( $trackNumbers ) {
		$url = $this->config->getHost() . self::TDWS_17TRACK_API_VERSION . self::TDWS_17TRACK_REGISTER_URI;
		return $this->baseRequest($trackNumbers, $url);
	}	

	/**
	 * stop multi tracking api function
	 *
	 * @since    1.1.0
	 */
	public function stopTrackingMulti( $trackNumbers ) {
		$url = $this->config->getHost() . self::TDWS_17TRACK_API_VERSION . self::TDWS_17TRACK_STOP_TRACK_URI;
		return $this->baseRequest($trackNumbers, $url);
	}	

	/**
	 * change multi tracking carrier api function
	 *
	 * @since    1.1.0
	 */
	public function changeCarrierMulti( $trackNumbers ) {
		$url = $this->config->getHost() . self::TDWS_17TRACK_API_VERSION . self::TDWS_17TRACK_CHANGE_CARRIER_URI;
		return $this->baseRequest($trackNumbers, $url);
	}

	/**
	 * get multi tracking api function
	 *
	 * @since    1.1.0
	 */
	public function getTrackInfoMulti( $trackNumbers ) {
		$url = $this->config->getHost() . self::TDWS_17TRACK_API_VERSION . self::TDWS_17TRACK_GET_TRACK_INFO_URI;
		return $this->baseRequest($trackNumbers, $url);
	}

	/**
	 * get tracking list api function
	 *
	 * @since    1.1.0
	 */
	public function getTrackList( $args ) {
		$url = $this->config->getHost() . self::TDWS_17TRACK_API_VERSION . self::TDWS_17TRACK_GET_TRACK_LIST_URI;
		return $this->baseRequest($args, $url);
	}

	/**
	 * get re tracking multi api function
	 *
	 * @since    1.1.0
	 */
	public function reTrackMulti( $trackNumbers ) {
		$url = $this->config->getHost() . self::TDWS_17TRACK_API_VERSION . self::TDWS_17TRACK_RE_TRACK_URI;
		return $this->baseRequest($trackNumbers, $url);
	}

	/**
	* get delete tracking multi api function
	*
	* @since    1.1.0
	*/
	public function deleteTrackMulti( $trackNumbers ) {
		$url = $this->config->getHost() . self::TDWS_17TRACK_API_VERSION . self::TDWS_17TRACK_DELETE_TRACK_URI;
		return $this->baseRequest($trackNumbers, $url);
	}


	/**
	 * Post for base request function
	 *
	 * @since    1.1.0
	 */
	public function baseRequest( $trackNumbers, $url ) {
		$headers = ['17token: '.$this->apiKey];
		 // Initialize cURL
		$ch = curl_init();

    // Encode the track numbers to JSON
		$jsonTrackNumbers = json_encode($trackNumbers);

    // Set cURL options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonTrackNumbers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['Content-Type: application/json'], $headers));

    // Execute the request
		$response = curl_exec($ch);

    // Check for errors
		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
			curl_close($ch);
			$this->tdws_trackMethodCallExceptionMessage($url, $exception->getMessage());
		}

    // Close cURL
		curl_close($ch);

    // Decode the response to an array
		return json_decode($response, true);

	}

	/**
	 * Check error function
	 *
	 * @since    1.1.0
	 */
	public function checkErrors( $response, $url ) {
		$error_msg = '';
		if (isset($response['data']['rejected']) && !empty($response['data']['rejected'])) {
			$errorCode = $response['data']['rejected'][0]['error']['code'];
			$errorMessage = $response['data']['rejected'][0]['error']['message'];
			$error_msg = $this->tdws_trackMethodCallExceptionMessage($url, $errorMessage, $errorCode);
		}

		if ( isset($response['data']['errors']) && !empty($response['data']['errors'])) {
			$errorCode = $response['data']['errors'][0]['code'];
			$errorMessage = $response['data']['errors'][0]['message'];

			$error_msg = $this->tdws_trackMethodCallExceptionMessage($url, $errorMessage, $errorCode);
		}
		return $error_msg;
	}

	/**
	 * Check error event history function
	 *
	 * @since    1.1.0
	 */
	public function checkEventHistory( $trackInfo ) {
		if (isset($trackInfo['track']['z1']) && empty($trackInfo['track']['z1']) ) {
			$this->tdws_trackMethodCallExceptionMessage(self::TDWS_17TRACK_GET_TRACK_INFO_URI, "Track event history not found");
		}
	}

	/**
	 * Fire event method exception function
	 *
	 * @since    1.1.0
	 */
	public function tdws_trackMethodCallExceptionMessage( $urlCall = "", $message = "", $code = 0, Throwable $previous = null ) {
    // Construct the exception message
		$exceptionMessage = "Exception in api method: " . $urlCall . " with Error code: " . $code . " with message: " . $message;
    // Return the constructed message
		return $exceptionMessage;
	}
}
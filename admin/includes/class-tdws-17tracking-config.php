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


class TDWS_17TRACKING_Config {
    
    const TDWS_17TRACK_HOST = 'https://api.17track.net/track';
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $host;

    /**
     * WeldPayConfig constructor.
     * @param string $host
     * @param string $apiKey
     */
    public function __construct (string $apiKey, string $host = null ) {
        $this->host = $host;
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host ?? self::TDWS_17TRACK_HOST;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [
          'Content-Type' => 'application/json',
          '17token' => $this->apiKey
        ];
    }
}
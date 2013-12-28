<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/Classes/configwriter.php');
class Publisher
{
	public $requestUrl;
	public $domainName;
	public $domainKey;
	public $retries = 5;
	private $source = "phpp";
	private $invalidResponseMessage = "Invalid response received from server.";
	
	function __construct($domainKey="11111111-1111-1111-1111-111111111111", $domainName='localhost', $requestUrl="http://sync3.frozenmountain.com/request.ashx")
	{
		ini_set("track_errors", "1");
		$configs = configwriter::configs();
		$this->requestUrl = $requestUrl;
		$this->domainName = Publisher::sanitizeDomainName($configs['site_domain_name']);
		$this->domainKey = $domainKey;
	}
	
	private static function sanitizeDomainName($domainName)
	{
		if (strpos($domainName, "http://") === 0 || strpos($domainName, "https://") === 0)
		{
			return $domainName;
		}
		return "http://" . $domainName;
	}
	
	private static function addQueryToUrl($url, $key, $value = null)
	{
		if (empty($key))
		{
			return $url;
		}
		return $url . (substr_count($url, "?") > 0 ? "&" : "?") . urlencode($key) . "=" . urlencode($value);
	}
	
	// delivers an array of publications
	public function publish($publications)
	{
		// force an array of publication arrays
		if (array_key_exists('channel', $publications))
		{
			$publications = array($publications);
		}
		
		return $this->send($publications);
	}
	
	private function send($publications)
	{
		// serialize the publications
		$writeContent = Publisher::toJson($publications);
		
		// build the target
		$url = $this->requestUrl;
		$url = Publisher::addQueryToUrl($url, "key", $this->domainKey);
		$url = Publisher::addQueryToUrl($url, "src", $this->source);
		
		// post the publications
		$response = Publisher::post($url, $writeContent, "application/json", $this->domainName);
		
		// process the response
		if (empty($response))
		{
			// an empty response indicates total success
			foreach ($publications as &$publication)
			{
				$publication["successful"] = true;
				$publication["timestamp"] = date("Y-m-d") . "T" . date("H:i:s") . ".00";
			}
		}
		else
		{
			$publications = Publisher::fromJson($response);
		}
		return $publications;
	}
	
	private static function post($url, $data, $contentType, $referrer)
	{
		$referrer = Publisher::sanitizeDomainName($referrer);
		
		// set the request parameters
		$urlParts = parse_url($url);
		$options = array( 
			"http" => array( 
				"method" => "POST",
				"content" => $data,
				"protocol_version" => "1.0",
				"header" => 
					"Content-Type: " . $contentType . "\r\n" .
					"Referer: " . $referrer . "\r\n" .
					"Host: " . $urlParts["host"] . "\r\n"
			)
		);
		
		// get the stream context
		$context = stream_context_create($options);
		
		// open the pointer
		$pointer = @fopen($url, "r", false, $context);
		if (!$pointer)
		{
			throw new Exception("Problem writing data to $url, $php_errormsg");
		}
		
		// get the response
		$response = @stream_get_contents($pointer);
		if ($response === false)
		{
			throw new Exception("Problem reading data from $url, $php_errormsg");
		}
		return $response;
	}
	
	private static function toJson($publications)
	{
		$publicationJsons = array();
		foreach ($publications as $publication)
		{
			// string replace re: http://bugs.php.net/bug.php?id=49366
			$publicationJson = str_replace('\/', '/', json_encode($publication));
			$publicationJsons[] = $publicationJson;
		}
		return "[" . implode($publicationJsons, ",") . "]";
	}
	
	private static function fromJson($publicationsJson)
	{
		// decode the JSON into publications
		// stripslashes re: http://thefrontiergroup.com.au/blog/2008/11/json_decode_php/
		if (get_magic_quotes_gpc())
		{
			$publicationsJson = stripslashes($publicationsJson);
		}
		return json_decode($publicationsJson, true);
	}
	
	
}

?>
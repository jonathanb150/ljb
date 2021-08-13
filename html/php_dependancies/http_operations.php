<?php 
Class HTTPOperations {
	//Error handling variables
	private $maxRecursions = 10;
	private $errorRecursion = 0;

	//Info regarding last operation
	public $lastHTTPResponse = 0;
	public $lastHTTPOperationDetails = false;
	public $lastHTTPOperationSpeed = 0;
	public $lastHTTPOperation = null;
	public $lastHTTPOperationHeaderSize = 0;

	//Proxy information
	public $proxy = null;

	//Cookie information
	public $cookie = null;

	//Content encoding
	public $contentEncoding = null;

	//Custom headers
	public $http_headers = null;

	//HTTP authorization
	public $http_user = null;
	public $http_password = null;

	public function cURLDebug($url) {
		//Avoid bans & network overload
		usleep(random_int(200000, 300000));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, htmlspecialchars($url, ENT_QUOTES, 'UTF-8'));
		curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgent());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
		curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		if ($this->http_headers != null) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->http_headers);
		}
		if (stringInVariable('HTTPS', $url) || stringInVariable('https', $url)) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		if($this->contentEncoding != null) {
			curl_setopt($ch, CURLOPT_ENCODING, $this->contentEncoding);
		}
		if($this->http_user != null && $this->http_password != null) {
			curl_setopt($ch, CURLOPT_USERPWD, $this->http_user.":".$this->http_password);
		}
		$this->lastHTTPOperation = curl_exec($ch);

		//Error handling
		if (curl_getinfo($ch)['http_code'] != '200') {
			if ($this->errorRecursion < $this->maxRecursions) {
				$this->errorRecursion++;
				sleep(random_int(1, 2));
				return $this->cURLDebug($url);
			} else {
				$this->errorRecursion = 0;
			}
		}

		//Info
		$this->lastHTTPResponse = curl_getinfo($ch)['http_code'];
		$this->lastHTTPOperationDetails = curl_getinfo($ch);
		$this->lastHTTPOperationHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

		$response_headers = substr($this->lastHTTPOperation, 0, $this->lastHTTPOperationHeaderSize);
		$body = substr($this->lastHTTPOperation, $this->lastHTTPOperationHeaderSize);

		return "Request Headers: <br>".$this->lastHTTPOperationDetails['request_header']."<br><br>Response Headers: <br>".json_encode($response_headers, true)."<br><br>Complete Request Details: <br>".json_encode($this->lastHTTPOperationDetails, true)."<br><br>Body: ".$body[0];
	}

	public function cURL($url) {
		//Avoid bans & network overload
		usleep(random_int(200000, 300000));
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, htmlspecialchars($url, ENT_QUOTES, 'UTF-8'));
		curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgent());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
		curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		if ($this->http_headers != null) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->http_headers);
		}
		if (stringInVariable('HTTPS', $url) || stringInVariable('https', $url)) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		if($this->contentEncoding != null) {
			curl_setopt($ch, CURLOPT_ENCODING, $this->contentEncoding);
		}
		if($this->http_user != null && $this->http_password != null) {
			curl_setopt($ch, CURLOPT_USERPWD, $this->http_user.":".$this->http_password);
		}
		$html = curl_exec($ch);

		//Error handling
		if (curl_getinfo($ch)['http_code'] == '503') {
			if ($this->errorRecursion < $this->maxRecursions) {
				$this->errorRecursion++;
				usleep(random_int(500000, 1500000));
				return $this->cURL($url);
			} else {
				$this->errorRecursion = 0;
			}
		}

		//Info
		$this->lastHTTPResponse = curl_getinfo($ch)['http_code'];
		$this->lastHTTPOperationDetails = curl_getinfo($ch);
		if (isset(curl_getinfo($ch)['speed_download'])) {
			$this->lastHTTPOperationSpeed = curl_getinfo($ch)['speed_download'];
		}

		return $html;
	}

	public function cURLPost($url, $post_data) {
		//Avoid bans & network overload
		usleep(random_int(200000, 300000));
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, htmlspecialchars($url, ENT_QUOTES, 'UTF-8'));
		curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgent());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
		curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		if ($this->http_headers != null) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->http_headers);
		}
		if (stringInVariable('HTTPS', $url) || stringInVariable('https', $url)) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		if($this->contentEncoding != null) {
			curl_setopt($ch, CURLOPT_ENCODING, $this->contentEncoding);
		}
		if($this->http_user != null && $this->http_password != null) {
			curl_setopt($ch, CURLOPT_USERPWD, $this->http_user.":".$this->http_password);
		}
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		
		$html = curl_exec($ch);

		//Error handling
		if (curl_getinfo($ch)['http_code'] == '503') {
			if ($this->errorRecursion < $this->maxRecursions) {
				$this->errorRecursion++;
				usleep(random_int(500000, 1500000));
				return $this->cURL($url);
			} else {
				$this->errorRecursion = 0;
			}
		}

		//Info
		$this->lastHTTPResponse = curl_getinfo($ch)['http_code'];
		$this->lastHTTPOperationDetails = curl_getinfo($ch);
		if (isset(curl_getinfo($ch)['speed_download'])) {
			$this->lastHTTPOperationSpeed = curl_getinfo($ch)['speed_download'];
		}

		return $html;
	}

	public function cURLDownload($url, $save_directory) {
		//Avoid bans & network overload
		usleep(random_int(200000, 300000));

		$fp = fopen($save_directory, "w+");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
		curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		if ($this->http_headers != null) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->http_headers);
		}
		curl_setopt($ch, CURLOPT_FILE, $fp);   
		if($this->contentEncoding != null) {
			curl_setopt($ch, CURLOPT_ENCODING, $this->contentEncoding);
		}
		if($this->http_user != null && $this->http_password != null) {
			curl_setopt($ch, CURLOPT_USERPWD, $this->http_user.":".$this->http_password);
		}
		curl_exec($ch);

		//Info
		$this->lastHTTPResponse = curl_getinfo($ch)['http_code'];
		$this->lastHTTPOperationDetails = curl_getinfo($ch);
		if (isset(curl_getinfo($ch)['speed_download'])) {
			$this->lastHTTPOperationSpeed = curl_getinfo($ch)['speed_download'];
		}

		//Error handling
		if ($this->downloadSuccessful($save_directory)) {
			return true;
		} else {
			if ($this->errorRecursion < $this->maxRecursions) {
				$this->errorRecursion++;
				usleep(random_int(500000, 1500000));
				return $this->cURLDownload($url, $save_directory);
			} else {
				$this->errorRecursion = 0;
				return false;
			}
		}
	}

	private function downloadSuccessful($save_directory) {
		if ($this->lastHTTPOperationDetails['http_code'] == 200 && $this->lastHTTPOperationDetails['size_download'] > 0 && $this->lastHTTPOperationDetails['download_content_length'] > 0 && $this->lastHTTPOperationDetails['size_download'] == $this->lastHTTPOperationDetails['download_content_length'] && filesize($save_directory) == $this->lastHTTPOperationDetails['download_content_length']) {
			return true;
		}
		return false;
	}

	//Get random user agent
	private function getUserAgent() {
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0.1 Safari/604.3.5";
		$userAgentArray[] = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.89 Safari/537.36 OPR/49.0.2725.47";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/604.4.7 (KHTML, like Gecko) Version/11.0.2 Safari/604.4.7";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:57.0) Gecko/20100101 Firefox/57.0";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36 Edge/15.15063";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:57.0) Gecko/20100101 Firefox/57.0";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36 Edge/16.16299";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/604.4.7 (KHTML, like Gecko) Version/11.0.2 Safari/604.4.7";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0.1 Safari/604.3.5";
		$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:52.0) Gecko/20100101 Firefox/52.0";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 OPR/49.0.2725.64";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; rv:57.0) Gecko/20100101 Firefox/57.0";
		$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/604.4.7 (KHTML, like Gecko) Version/11.0.2 Safari/604.4.7";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:57.0) Gecko/20100101 Firefox/57.0";
		$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/62.0.3202.94 Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:56.0) Gecko/20100101 Firefox/56.0";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:58.0) Gecko/20100101 Firefox/58.0";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0";
		$userAgentArray[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0;  Trident/5.0)";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; rv:52.0) Gecko/20100101 Firefox/52.0";
		$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/63.0.3239.84 Chrome/63.0.3239.84 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0";
		$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.89 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0;  Trident/5.0)";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:57.0) Gecko/20100101 Firefox/57.0";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0.1 Safari/604.3.5";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:57.0) Gecko/20100101 Firefox/57.0";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0";
		$userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 11_1_2 like Mac OS X) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0 Mobile/15B202 Safari/604.1";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; Touch; rv:11.0) like Gecko";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:58.0) Gecko/20100101 Firefox/58.0";
		$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Safari/604.1.38";
		$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
		$userAgentArray[] = "Mozilla/5.0 (X11; CrOS x86_64 9901.77.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.97 Safari/537.36";
		
		$getArrayKey = array_rand($userAgentArray);
		return $userAgentArray[$getArrayKey];
	}
}
?>
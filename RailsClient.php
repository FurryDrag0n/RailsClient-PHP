<?php
class RailsClient {
    private $baseUrl;
    private $cookieFile;
    private $isLoggedIn = false;
    
    public function __construct($baseUrl = 'https://dw.y-chain.net/rails') {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->cookieFile = sys_get_temp_dir() . '/rails_cookie_' . uniqid() . '.txt';
    }
    
    public function __destruct() {
        if (file_exists($this->cookieFile))
            unlink($this->cookieFile);
    }
    
    public function auth($username, $password, $confirm = null) {
        $data = [
            'username' => $username,
            'password' => $password
        ];
		
		if ($confirm !== null)
            $data['confirm'] = $confirm;
        
        $result = $this->makeRequest('/auth.php', $data);
        
        if (isset($result['message']) && $result['message'] === 'auth_success')
            $this->isLoggedIn = true;
        
        return $result;
    }
    
    public function registerGame($title, $picUrl, $authUrl, $description, $price = null) {
        if (!$this->isLoggedIn)
            return [
                'message' => 'not_authenticated',
                'errors' => ['You must be logged in to register a game']
            ];
        
        $data = [
            'title' => $title,
            'pic_url' => $picUrl,
            'auth_url' => $authUrl,
            'description' => $description
        ];
        
        if ($price !== null)
            $data['price'] = $price;
        
        return $this->makeRequest('/new_game.php', $data);
    }
	
	public function createInvoice($amount, $payload = '') {
        if (!$this->isLoggedIn)
            return [
                'message' => 'not_authenticated',
                'errors' => ['You must be logged in to create an invoice']
            ];

        $data = [
            'amount' => $amount,
        ];
        
        if (!empty($payload))
            $data['payload'] = $payload;

        return $this->makeRequest('/invoice.php', $data);
    }

	public function makePayout($username, $password, $amount, $recipient, $type = 'user') {
        if (!$this->isLoggedIn)
            return [
                'message' => 'not_authenticated',
                'errors' => ['You must be logged in to create an invoice']
            ];

        $data = [
			'username' => $username,
			'password' => $password,
            'amount' => $amount,
			'recipient' => $recipient,
			'type' => $type
        ];

        return $this->makeRequest('/payout.php', $data);
    }
	
	public function userInfo($token = null) {
        if (!$this->isLoggedIn)
            return [
                'message' => 'not_authenticated',
                'errors' => ['You must be logged in to view user information']
            ];
		
		$params = [];
		
		if ($token !== null)
			$params['token'] = $token;
		
		$queryString = http_build_query($params);
        
        return $this->makeRequest('/uinfo.php?' . $queryString, [], 'GET');
    }
	
	public function internalPayment($username, $password, $invoiceId) {
        $data = [
            'username' => $username,
            'password' => $password,
            'invoice_id' => $invoiceId
        ];

        return $this->makeRequest('/internal_payment.php', $data);
    }
	
	public function getInvoice($invoiceId) {
        if (!$this->isLoggedIn)
            return [
                'message' => 'not_authenticated',
                'errors' => ['You must be logged in to view invoices']
            ];

        $params = [
            'invoice_id' => $invoiceId
        ];

        $queryString = http_build_query($params);
        
        return $this->makeRequest('/receipt.php?' . $queryString, [], 'GET');
    }
	
	public function listGames($page = 1, $limit = 50, $onlyFree = false) {
        $params = [
            'page' => $page,
            'limit' => $limit,
            'only_free' => $onlyFree ? 'true' : 'false'
        ];
        
        $queryString = http_build_query($params);
        
        return $this->makeRequest('/games.php?' . $queryString, [], 'GET');
    }
	
	public function searchGames($searchBy, $request, $page = 1, $limit = 50, $onlyFree = false) {
		$validSearchCriteria = ['title', 'author'];
		if (!in_array($searchBy, $validSearchCriteria))
			throw new InvalidArgumentException("Invalid search criteria. Must be one of: " . implode(', ', $validSearchCriteria));
		
		$params = [
			'page' => $page,
			'limit' => $limit,
			'only_free' => $onlyFree ? 'true' : 'false',
			'search_by' => $searchBy,
			'request' => $request
		];
		
		$queryString = http_build_query($params);
		
		return $this->makeRequest('/games.php?' . $queryString, [], 'GET');
	}

	public function openGame($gameId) {
		if (!is_numeric($gameId))
			throw new InvalidArgumentException("Game ID must be a numeric value.");
		
		$params = [
			'search_by' => 'id',
			'request' => (string)$gameId
		];
		
		$queryString = http_build_query($params);
		
		return $this->makeRequest('/games.php?' . $queryString, [], 'GET');
	}
	
	public function purchaseGame($gameId) {
        if (!$this->isLoggedIn)
            return [
                'message' => 'not_authenticated',
                'errors' => ['You must be logged in to view user information']
            ];
			
		if (!is_numeric($gameId))
			throw new InvalidArgumentException("Game ID must be a numeric value.");
			
		$data = [
			'game_id' => (string)$gameId
		];
        
        return $this->makeRequest('/get_game.php', $data);
    }
	
	public function getToken($gameId) {
        if (!$this->isLoggedIn)
            return [
                'message' => 'not_authenticated',
                'errors' => ['You must be logged in to view user information']
            ];
			
		if (!is_numeric($gameId))
			throw new InvalidArgumentException("Game ID must be a numeric value.");
			
		$data = [
			'game_id' => (string)$gameId
		];
        
        return $this->makeRequest('/get_token.php', $data);
    }
	
	public function unauth() {
        $this->isLoggedIn = false;
        if (file_exists($this->cookieFile))
            unlink($this->cookieFile);
        
		return $this->makeRequest('/unauth.php', []);
    }
    
    private function makeRequest($endpoint, $data, $method = 'POST') {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_USERAGENT => 'GameApiClient/1.0',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error)
            return [
                'message' => 'curl_error',
                'errors' => [$error]
            ];
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE)
            return [
                'message' => 'invalid_json',
                'errors' => ['Invalid JSON response from server'],
                'raw_response' => $response,
                'http_code' => $httpCode
            ];
        
        return $decoded;
    }
    
    public function isAuthenticated() {
        return $this->isLoggedIn;
    }

}

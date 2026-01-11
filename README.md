# RailsClient PHP Class Documentation

## Overview

The `RailsClient` class is a comprehensive PHP wrapper for the Rails Game Store API. It provides a convenient, object-oriented interface for interacting with all API endpoints, handling session management, and processing responses.

## Installation

Simply include the `RailsClient.php` file in your project:

```php
require_once 'RailsClient.php';
```

## Basic Usage

```php
// Create a client instance
$client = new RailsClient();

// Authenticate
$result = $client->auth('username', 'password');

// Check if authenticated
if ($client->isAuthenticated()) {
    // Make authenticated requests
    $games = $client->listGames();
}

// Log out when done
$client->unauth();
```

## Constructor

```php
public function __construct($baseUrl = 'https://dw.y-chain.net/rails')
```

**Parameters:**
- `$baseUrl` (string): Base URL of the Rails API (default: `'https://dw.y-chain.net/rails'`)

**Example:**
```php
// Use default URL
$client = new RailsClient();

// Use custom URL
$client = new RailsClient('https://api.example.com/rails');
```

## Authentication Methods

### `auth()` - Login or Register
```php
public function auth($username, $password, $confirm = null)
```

**Parameters:**
- `$username` (string): Username
- `$password` (string): Password
- `$confirm` (string|null): Password confirmation (optional, triggers registration)

**Behavior:**
- If `$confirm` is provided: Performs user registration
- If `$confirm` is null: Performs user login

**Example:**
```php
// Login
$result = $client->auth('username', 'password');

// Register
$result = $client->auth('username', 'password', 'password');
```

### `unauth()` - Logout
```php
public function unauth()
```

**Description:** Destroys the current session and removes session cookies.

**Example:**
```php
$result = $client->unauth();
```

### `isAuthenticated()` - Check Login Status
```php
public function isAuthenticated()
```

**Returns:** `bool` - `true` if user is logged in, `false` otherwise

## User Management

### `userInfo()` - Get User Information
```php
public function userInfo($token = null)
```

**Parameters:**
- `$token` (string|null): Game authentication token (optional)

**Note:** Requires authentication

**Example:**
```php
// Get current user info
$userInfo = $client->userInfo();

// Get user info for a game token
$userInfo = $client->userInfo('game_token_123');
```

## Game Management

### `listGames()` - List Games
```php
public function listGames($page = 1, $limit = 50, $onlyFree = false)
```

**Parameters:**
- `$page` (int): Page number (default: 1)
- `$limit` (int): Items per page (1-100, default: 50)
- `$onlyFree` (bool): Show only free games (default: false)

**Example:**
```php
// Get first page of all games
$games = $client->listGames(1, 20);

// Get free games only
$freeGames = $client->listGames(1, 20, true);
```

### `searchGames()` - Search Games
```php
public function searchGames($searchBy, $request, $page = 1, $limit = 50, $onlyFree = false)
```

**Parameters:**
- `$searchBy` (string): Search criteria: 'title' or 'author'
- `$request` (string): Search query
- `$page` (int): Page number (default: 1)
- `$limit` (int): Items per page (default: 50)
- `$onlyFree` (bool): Show only free games (default: false)

**Throws:** `InvalidArgumentException` for invalid search criteria

**Example:**
```php
// Search by title
$results = $client->searchGames('title', 'adventure');

// Search by author
$results = $client->searchGames('author', 'silent58');
```

### `openGame()` - Get Game by ID
```php
public function openGame($gameId)
```

**Parameters:**
- `$gameId` (int|string): Game ID

**Throws:** `InvalidArgumentException` for non-numeric game ID

**Example:**
```php
$game = $client->openGame(123);
```

### `registerGame()` - Register New Game
```php
public function registerGame($title, $picUrl, $authUrl, $description, $price = null)
```

**Parameters:**
- `$title` (string): Game title
- `$picUrl` (string): URL to game cover image
- `$authUrl` (string): Game authentication URL
- `$description` (string): Game description
- `$price` (float|null): Price in NVC (optional, null for free games)

**Note:** Requires authentication

**Example:**
```php
$result = $client->registerGame(
    'My Awesome Game',
    'https://example.com/cover.jpg',
    'https://game.example.com/auth',
    'An exciting adventure game',
    1.5 // Price in NVC
);
```

## Game Purchase

### `purchaseGame()` - Purchase a Game
```php
public function purchaseGame($gameId)
```

**Parameters:**
- `$gameId` (int|string): Game ID to purchase

**Note:** Requires authentication

**Throws:** `InvalidArgumentException` for non-numeric game ID

**Behavior:**
- For free games: Immediately adds to user's library
- For paid games: Creates an invoice for payment

**Example:**
```php
$result = $client->purchaseGame(123);
```

## Authentication Tokens

### `getToken()` - Generate Game Auth Token
```php
public function getToken($gameId)
```

**Parameters:**
- `$gameId` (int|string): Game ID

**Note:** Requires authentication

**Throws:** `InvalidArgumentException` for non-numeric game ID

**Example:**
```php
$tokenResult = $client->getToken(123);
```

## Invoice Management

### `createInvoice()` - Create Payment Invoice
```php
public function createInvoice($amount, $payload = '')
```

**Parameters:**
- `$amount` (float): Payment amount in NVC
- `$payload` (string): Optional metadata (max 255 chars)

**Note:** Requires authentication

**Example:**
```php
$invoice = $client->createInvoice(1.5, 'game_purchase');
```

### `getInvoice()` - Get Invoice Details
```php
public function getInvoice($invoiceId)
```

**Parameters:**
- `$invoiceId` (int): Invoice ID

**Note:** Requires authentication

**Example:**
```php
$invoice = $client->getInvoice(12345);
```

### `internalPayment()` - Process Internal Payment
```php
public function internalPayment($username, $password, $invoiceId)
```

**Parameters:**
- `$username` (string): Username
- `$password` (string): Password
- `$invoiceId` (int): Invoice ID

**Example:**
```php
$payment = $client->internalPayment('user', 'pass', 12345);
```

## Complete Examples

### Example 1: Browse and Purchase Games
```php
$client = new RailsClient();

// Login
$client->auth('username', 'password');

// Browse games
$games = $client->listGames(1, 10);

// Get details of a specific game
$game = $client->openGame(123);

// Purchase the game
$purchase = $client->purchaseGame(123);

// Logout
$client->unauth();
```

### Example 2: Game Developer Workflow
```php
$client = new RailsClient();
$client->auth('developer', 'password');

// Register a new game
$game = $client->registerGame(
    'New Puzzle Game',
    'https://example.com/puzzle.jpg',
    'https://puzzle.example.com/auth',
    'A challenging puzzle game for all ages',
    0.5 // Price in NVC
);

// Check the created invoice
$invoice = $client->getInvoice($game['data']['invoice_id']);

// Generate auth token for testing
$token = $client->getToken($game['data']['game_id']);

$client->unauth();
```

## Error Handling

The class handles various errors gracefully:

1. **Invalid JSON responses:** Returns error message with raw response
2. **Network errors:** Returns curl error details
3. **Invalid parameters:** Throws `InvalidArgumentException`
4. **Authentication required:** Returns `not_authenticated` error

**Error Response Format:**
```php
[
    'message' => 'error_type',
    'errors' => ['Error message 1', 'Error message 2'],
    // Additional fields may be present depending on error type
]
```

## Session Management

- Sessions are automatically maintained using cookies stored in a temporary file
- The cookie file is automatically cleaned up when the object is destroyed
- Use `unauth()` to explicitly log out and remove the cookie file

## Dependencies

- PHP 7.0 or higher
- cURL extension enabled
- JSON extension enabled

## API Base URL

Default: `https://dw.y-chain.net/rails/`

All endpoints follow the pattern: `{baseUrl}/{endpoint}.php`

## Response Format

All methods return arrays with the following structure:

```php
[
    'status' => 'success_status',
    'data' => [/* response data */],
    'errors' => null // or array of error messages
]
```

## Notes

1. **Authentication Required:** Most methods require the user to be authenticated first using `auth()`
2. **Auto-logout:** The session automatically ends when the object is destroyed
3. **Thread Safety:** Not thread-safe due to cookie file usage
4. **Timeouts:** Default 30-second timeout for all requests
5. **SSL Verification:** Disabled by default (can be modified in `makeRequest` method)


**API Documentation:** See the full API documentation for detailed endpoint specifications and response formats.

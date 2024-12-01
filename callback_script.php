<?php
// Database credentials
$host = 'localhost';
$dbname = 'scraper';
$username = 'root';
$password = '';

// Set JSON response headers
header('Content-Type: application/json');

// Database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    logData('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
    exit;
}

// Function to log data to a file
function logData($message) {
    file_put_contents('callback_log.txt', date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}

// Validate and process product data
function processProductData($data) {
    global $pdo;
    foreach ($data as $product) {
        if (empty($product['Product Name']) || empty($product['Product Image URL']) || !isset($product['Product Price'])) {
            logData('Skipped invalid product data: ' . json_encode($product));
            continue;
        }

        try {
            // Check for duplicates based on product name
            $checkQuery = "SELECT COUNT(*) FROM products WHERE name = :name";
            $checkStmt = $pdo->prepare($checkQuery);
            $checkStmt->bindParam(':name', $product['Product Name']);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() > 0) {
                logData('Duplicate product skipped: ' . json_encode($product));
                continue;
            }

            // Insert product into database
            $sql = "INSERT INTO products (name, price, image_url) VALUES (:name, :price, :image_url)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $product['Product Name']);
            $stmt->bindParam(':price', $product['Product Price']);
            $stmt->bindParam(':image_url', $product['Product Image URL']);
            $stmt->execute();

            logData('Product saved successfully: ' . json_encode($product));
        } catch (PDOException $e) {
            logData('Failed to insert product: ' . json_encode($product) . ' | Error: ' . $e->getMessage());
        }
    }
}

// Handle incoming POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
        processProductData($data);
        echo json_encode(['status' => 'success', 'message' => 'Data processed successfully']);
    } else {
        logData('Invalid JSON received: ' . json_last_error_msg());
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON format']);
    }
} else {
    logData('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method', 'allowed_methods' => ['POST']]);
}
?>

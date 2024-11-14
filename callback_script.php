<?php
// Database connection settings
$host = 'localhost';
$dbname = 'scraper';
$username = 'root';
$password = ''; // Adjust as needed for your setup

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function save_data($products) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO products (name, price, image_url, description) VALUES (?, ?, ?, ?)");

    foreach ($products as $product) {
        $name = filter_var($product['name'], FILTER_SANITIZE_STRING);
        $price = filter_var($product['price'], FILTER_VALIDATE_FLOAT);
        $image_url = filter_var($product['image_url'], FILTER_SANITIZE_URL);
        $description = $product['description'] ?? '';

        if ($price !== false) {
            $stmt->execute([$name, $price, $image_url, $description]);
        } else {
            file_put_contents("error.log", "Invalid price for product: " . json_encode($product) . "\n", FILE_APPEND);
        }
    }
}

// Check for POST data
$data = json_decode(file_get_contents("php://input"), true);
if (isset($data['product_data']) && is_array($data['product_data'])) {
    save_data($data['product_data']);
    echo json_encode(["status" => "success", "message" => "Data saved successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid product data"]);
}
?>

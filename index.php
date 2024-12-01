<?php
ini_set('error_log', __DIR__ . '/scraper_debug.log');

// Fetch HTML content of a URL
function fetchHTML($url) {
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0 (compatible; PHP Web Scraper)\r\n"
        ]
    ];
    $context = stream_context_create($options);
    $html = file_get_contents($url, false, $context);

    if ($html === false) {
        file_put_contents('debug.html', $html);
        return ['error' => 'Failed to fetch the URL content.'];
    }

    return $html;
}

// Scrape product data
function scrapeProductData($html) {
    $productData = [];
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);

    if (!$dom->loadHTML($html)) {
        file_put_contents('debug.log', "Failed to load HTML", FILE_APPEND);
        return ['error' => 'Failed to load HTML'];
    }

    $xpath = new DOMXPath($dom);
    $items = $xpath->query('//div[contains(@class, "list-item")]');

    foreach ($items as $item) {
        $product = [];
        $nameNode = $xpath->query('.//h3[@itemprop="name"]', $item);
        if ($nameNode->length > 0) {
            $product['Product Name'] = trim($nameNode->item(0)->nodeValue);
        }

        $imageNode = $xpath->query('.//img[@itemprop="image"]', $item);
        if ($imageNode->length > 0) {
            $product['Product Image URL'] = trim($imageNode->item(0)->getAttribute('src'));
        }

        $priceNode = $xpath->query('.//meta[@itemprop="price"]', $item);
        if ($priceNode->length > 0) {
            $priceString = trim($priceNode->item(0)->getAttribute('content'));
            $cleanPrice = str_replace(',', '.', preg_replace('/[^\d,\.]/', '', $priceString));
            $product['Product Price'] = (float)$cleanPrice;
        }

        if (!empty($product)) {
            $productData[] = $product;
        }
    }

    return $productData;
}

// Send data to callback
function sendDataToCallback($data, $callbackUrl) {
    $jsonData = json_encode($data);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $jsonData,
        ]
    ]);

    $response = file_get_contents($callbackUrl, false, $context);

    logData('Batch Sent to Callback: ' . $jsonData);
    logData('Callback Response: ' . $response);
}


// Log data
function logData($message) {
    file_put_contents('scraper_log.txt', date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}

// Scrape and send in batches
function scrapeAndSend($url, $callbackUrl, &$allProducts) {
    $html = fetchHTML($url);
    if (is_array($html) && isset($html['error'])) {
        logData('Error fetching URL: ' . json_encode($html));
        return;
    }

    $products = scrapeProductData($html);
    $allProducts = $products; // Preserve all products for response

    if (empty($products)) {
        logData('No products found for URL: ' . $url);
        return;
    }

    $batchSize = 4;
    $batches = array_chunk($products, $batchSize);

    foreach ($batches as $batch) {
        sendDataToCallback($batch, $callbackUrl);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pageUrl = $_POST['page_url'] ?? '';
    $callbackUrl = $_POST['callback_url'] ?? '';

    if (!filter_var($pageUrl, FILTER_VALIDATE_URL) || !filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
        echo json_encode(['error' => 'Invalid URL']);
        exit;
    }

    $allProducts = [];
    scrapeAndSend($pageUrl, $callbackUrl, $allProducts);

    // Output JSON response with all products
    header('Content-Type: application/json');
    echo json_encode($allProducts, JSON_PRETTY_PRINT);
    exit;
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Scraper and API Builder - Core PHP</title>
</head>
<body>
    <h1>Product Scraper</h1>
    <form method="POST" action="index.php">
        <label for="url">Page URL:</label>
        <input type="text" id="url" name="page_url" required><br><br>
        <label for="callback_url">Callback URL:</label>
        <input type="text" id="callback_url" name="callback_url" required><br><br>
        <button type="submit">Scrape</button>
    </form>
</body>
</html>
<?php
}
?>

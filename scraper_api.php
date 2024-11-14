<?php

function getPageContent($url) {
    // Using file_get_contents to get the content of the page
    $html = file_get_contents($url); 
    return $html;
}

function formatPrice($price) {
    // Remove non-numeric characters except for the decimal point
    $price = preg_replace('/[^\d]/', '', $price);
    
    // Convert the price string to an integer (if it's not already)
    $price = (int)$price;
    
    // Divide by 100 to get the correct price format
    return $price / 100;
}

function scrapeProductData($url) {
    // Step 1: Get HTML content of the product page
    $html = getPageContent($url);
    
    // Step 2: Load HTML into DOMDocument
    $dom = new DOMDocument;
    @$dom->loadHTML($html);

    // Step 3: Create a DOMXPath object for querying the DOM
    $xpath = new DOMXPath($dom);

    // Step 4: Extract product details using XPath queries

    // Get all product name nodes
    $nameNodes = $xpath->query("//h3[@itemprop='name']");

    // Get all product image nodes
    $imageNodes = $xpath->query("//img[@itemprop='image']");

    // Get all product price nodes
    $priceNodes = $xpath->query("//div[@class='price']");

    // Limit to a maximum of 4 products
    $maxProducts = 4;
    $productData = [];

    // Loop through each product (up to the maxProducts limit)
    for ($i = 0; $i < min($maxProducts, $nameNodes->length); $i++) {
        // Get the product name
        $name = $nameNodes->item($i)->nodeValue;

        // Get the product image URL
        $imageUrl = $imageNodes->item($i)->getAttribute('src');

        // Get the product price
        $price = $priceNodes->item($i)->nodeValue;

        // Format the price for storage in a float/double column
        $formattedPrice = formatPrice($price);

        // Add product data to array
        $productData[] = [
            'name' => trim($name),
            'price' => $formattedPrice,
            'image_url' => trim($imageUrl)
        ];
    }

    return $productData;
}

// Example usage
$url = "https://catmobile.ro/huse-iphone-16/";
$productData = scrapeProductData($url);

// Output the scraped product data
print_r($productData);

function sendProductDataToDatabase($productData) {
    $url = "http://localhost/workplace/Web%20Scraper%20and%20API%20Builder/callback_script.php"; // Adjust URL as needed

    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['product_data' => $productData]));

    // Execute cURL request and capture response
    $response = curl_exec($ch);

    // Close cURL session
    curl_close($ch);

    // Handle response
    echo "Response from callback_script.php: " . $response;
}

// Example usage
$url = "https://catmobile.ro/huse-iphone-16/";
$productData = scrapeProductData($url);

// Send product data to callback_script.php
sendProductDataToDatabase($productData);


?>

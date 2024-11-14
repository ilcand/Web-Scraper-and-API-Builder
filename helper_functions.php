<?php
// ! Helper functions to extract data from product HTML

function extractName($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);  // The "@" suppresses warnings for malformed HTML.
    $xpath = new DOMXPath($dom);

    // Adjust the XPath based on the target site structure
    $nameNode = $xpath->query("(//div[contains(@class, 'Zhr-fS')])[1]");

    // Check if the name node was found and return the product name
    return $nameNode->length > 0 ? trim($nameNode->item(0)->textContent) : null;
}

function extractPrice($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Adjust the XPath based on the target site structure
    $priceNode = $xpath->query("//span[contains(@class, 'product-price-class')]");
    $price = $priceNode->length > 0 ? trim($priceNode->item(0)->textContent) : null;

    // Convert to numeric format (assuming price might have currency symbols)
    return is_numeric($price) ? floatval($price) : preg_replace('/[^\d.]/', '', $price);
}

function extractImageUrl($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Adjust the XPath based on the target site structure
    $imageNode = $xpath->query("//img[contains(@class, 'product-image-class')]");
    return $imageNode->length > 0 ? $imageNode->item(0)->getAttribute('src') : null;
}

function extractDescription($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Adjust the XPath based on the target site structure
    $descNode = $xpath->query("//p[contains(@class, 'product-description-class')]");
    return $descNode->length > 0 ? trim($descNode->item(0)->textContent) : null;
}

// function scrapeProducts($html) {
//     $products = [];
//     $dom = new DOMDocument();
//     @$dom->loadHTML($html);
//     $xpath = new DOMXPath($dom);

//     // Adjust the XPath to select individual product containers
//     $productNodes = $xpath->query("//div[contains(@class, 'product-container-class')]");
//     foreach ($productNodes as $productNode) {
//         $productHtml = $dom->saveHTML($productNode);
//         $products[] = [
//             'name' => extractName($productHtml),
//             'price' => extractPrice($productHtml),
//             'image_url' => extractImageUrl($productHtml),
//             'description' => extractDescription($productHtml)
//         ];
//     }
//     return $products;
// }


?>
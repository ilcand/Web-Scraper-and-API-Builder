Web Scraper and API Builder

Overview
Web Scraper and API Builder is a PHP-based project designed to scrape product data from static e-commerce webpages and expose it via a REST API. The extracted data includes product names, prices, and image URLs, which are processed and stored in a MySQL database in batches of 4.
This project was developed as part of a technical test during a job interview.

Features
- Web Scraping: Extracts product data (name, price, and image URL) from static HTML pages.
- REST API: Provides a convenient way to access and send scraped data to a specified callback URL.
- Batch Processing: Data is processed and saved in groups of 4 to optimize database operations.
- Request Tracking: Logs API requests for better traceability.
- Error Handling: Includes mechanisms to validate data and report scraping issues.
  
Usage
1. Configure the scraping target URL and database connection in the script.
2. Run the script to start scraping and sending data to the defined callback URL.
3. Access scraped data via the API or check your database for results.
   
Limitations
This scraper is designed for static webpages only. For websites that load content dynamically (e.g., via JavaScript), the scraper may not function correctly.
If you require support for dynamic content scraping, please contact me at andrei.ilca97@gmail.com for further assistance.

Disclaimer
This project was built for a technical test as part of a job interview process. It is optimized for the specific use case provided during the test and may need adjustments for other purposes.

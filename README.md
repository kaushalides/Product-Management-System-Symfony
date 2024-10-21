# Product Management Application

## Overview

This project is a web application developed using Symfony 7 that manages a list of products. It implements a complete CRUD (Create, Read, Update, Delete) functionality, allowing users to interact with product data efficiently. The application is built following best practices and utilizes the Doctrine ORM for database interactions.

## Features

1. **Product Table**: 
   - A database table for products with the following fields:
     - ID (auto-incrementing)
     - Name (string)
     - Description (text)
     - Price (decimal)
     - Stock Quantity (integer)
     - Created Datetime (datetime)

2. **Display Products**: 
   - A page that lists all products in a table format, displaying the created datetime in Singapore timezone.

3. **Sorting**: 
   - Sorting functionality for table columns: Name, Price, Stock Quantity, and Created Datetime.

4. **Searching**: 
   - Filtering options to search by price range, stock quantity, or the date the product was added.

5. **Pagination**: 
   - Pagination for the product list to handle large datasets effectively.

6. **Import/Export**: 
   - Functionality to import product data from a CSV file and export the current list of products to a CSV file.

7. **Add/Edit/Delete**: 
   - Options to add new products, edit existing ones, and delete products from the list.

8. **Error Handling**: 
   - Enhanced error handling to display user-friendly messages and log errors for debugging purposes.

9. **Performance Optimization**: 
   - Optimized database queries and application performance, especially for large datasets.

10. **Responsive Design**: 
    - Mobile-friendly and responsive design across different screen sizes.

11. **Data Validation**: 
    - Thorough server-side validation for all product fields to ensure data integrity.

## Technology Stack

- Symfony 7
- Doctrine ORM
- Twig Templates
- Bootstrap (for responsive design)
- PHP
- MySQL 

## Installation

Follow these steps to set up and run the application locally:

1. **Clone the repository**:
   Make sure you have Composer installed. Then run:

   ```bash
   git clone <repository-link>
   
2. Install dependencies:
   
   composer install

   
3. Set up your database:

    Configure your .env file with your database credentials.

4. Create the database:

    php bin/console doctrine:database:create

5. Run migrations:

   php bin/console doctrine:migrations:migrate

6. Run the application:

  Start the Symfony server:
  symfony serve

7. Access the application:

  Open your web browser and go to http://localhost:8000.

Usage
Navigate to the product management page to view, add, edit, or delete products.
Use the sorting and filtering options to manage the product list effectively.
Import or export product data as needed.


Acknowledgments
Symfony documentation for guidance on best practices.




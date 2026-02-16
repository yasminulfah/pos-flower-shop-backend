# POS Flower Shop API (Backend)

Welcome to the backend repository for the **POS Flower Shop (Uma Bloemist)** application. This is a Point of Sales system designed specifically for flower shops, covering product management (with variants), inventory, orders, shipping, and sales reports.

## 🚀 Key Features

This application provides a RESTful API for the following features:

*   **Authentication & Users**: Register, Login, Logout, and User Profile Management using Laravel Sanctum.
*   **Role Management**: Supports multi-role access such as **Owner**, **Admin**, and **Cashier**.
*   **Products & Categories**: 
    *   Category CRUD (Single Flower, Bouquet, Flower Box, etc.).
    *   Product CRUD with Variants (Color, Size, Custom Price).
    *   Stock Management per Variant.
*   **Order Management**:
    *   Order Checkout.
    *   Order History (My Orders).
    *   Update Order Status (Pending -> Completed/Cancelled).
    *   "Hold Order" feature and viewing "Pending Orders".
*   **Logistics**:
    *   Shipping Method Management.
    *   Packaging Management (Wrapping Paper, Box, etc.).
*   **Dashboard & Reports**:
    *   Dashboard Statistics for Owner/Admin.
    *   Sales Report Download (Excel).

## 🛠 Technology Stack

*   **Framework**: [Laravel 12](https://laravel.com)
*   **Database**: MySQL
*   **Authentication**: Laravel Sanctum
*   **Data Export**: Maatwebsite Excel
*   **API Testing**: Postman

## ⚙️ Installation & Setup

Follow these steps to run the project on your local machine:

1.  **Clone Repository**
    ```bash
    git clone https://github.com/yasminulfah/pos-flower-shop-backend.git
    cd pos-flower-shop
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    ```

3.  **Setup Environment**
    Copy the `.env.example` file to `.env`:
    ```bash
    cp .env.example .env
    ```
    Open the `.env` file and configure your database settings (DB_DATABASE, DB_USERNAME, DB_PASSWORD).

4.  **Generate App Key**
    ```bash
    php artisan key:generate
    ```

5.  **Migrate & Seed Database**
    Run database migrations and seed initial data (Default Users, Products, etc.):
    ```bash
    php artisan migrate --seed
    ```

6.  **Run Server**
    ```bash
    php artisan serve
    ```
    The application will be running at `http://localhost:8000`.

## 🔐 Default Accounts (Database Seeder)

After running `php artisan migrate --seed`, you can use the following accounts to login:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Owner** | `owner@umabloemist.com` | `password123` |
| **Admin** | `admin@umabloemist.com` | `password123` |
| **Cashier** | `yasmin@umabloemist.com` | `password123` |

## 📚 API Documentation

Inside the `/postman` folder, you will find Postman collection files that you can import to test the API:

*   **Collection**: `postman/pos-flower-shop.postman_collection.json`
*   **Environment**: `postman/pos-flower-shop.postman_environment.json`

Please import both files into your Postman application.

## 📝 License

[MIT license](https://opensource.org/licenses/MIT).

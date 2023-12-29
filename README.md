# Daily Agenda - Backend

This is the backend part of Daily Agenda, a full-stack todo list and weather app built with PHP and MySQL.

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Security Measures](#security-measures)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Environment Variables](#environment-variables)
  - [Installation](#installation)
  - [Database Setup](#database-setup)
- [API Endpoints](#api-endpoints)
  - [User Registration](#user-registration)
  - [User Login](#user-login)
  - [Fetch Todos](#fetch-todos)
  - [Insert Todo](#insert-todo)
  - [Update Todo](#update-todo)
  - [Delete Todo](#delete-todo)
- [Usage](#usage)
- [Technologies Used](#technologies-used)
- [Contact](#contact)

## Introduction

Welcome to the backend of Daily Agenda! This repository contains the server-side logic and database setup for the Daily Agenda application. The backend is built with PHP and MySQL, and it plays a crucial role in the overall functionality of the application.

The backend serves as the API for the frontend, handling all data-related operations. It provides endpoints for user registration and authentication, as well as for managing todos. When a user registers or logs in through the frontend, the backend validates the user credentials and generates a JSON Web Token (JWT) for secure sessions.

For todo management, the backend provides endpoints for creating, reading, updating, and deleting todos. The frontend communicates with these endpoints to perform the respective operations, sending requests to the backend and receiving responses.

The backend also interacts with a MySQL database to persist user and todo data. It uses the `vlucas/phpdotenv` package to manage environment variables, and the Firebase PHP-JWT library for handling JWTs.

In summary, the backend is responsible for processing requests from the frontend, interacting with the database, and returning responses back to the frontend. It ensures secure user authentication and efficient data management for the Daily Agenda application.

## Features

- **User Authentication:** Secure user registration and login using JSON Web Tokens (JWT).
- **Todo Management:** Handle the CRUD operations for registered user's todos.
- **Database Interaction:** Utilize MySQL to store and retrieve user data.

## Security Measures

The backend takes several measures to ensure the security of user data:

- **Data Validation:** All incoming data is validated before being processed. This ensures that only valid and expected data is sent to the database, preventing SQL injection attacks and other forms of data corruption.
- **Data Sanitization:** Incoming data is sanitized to remove any potentially harmful characters that could be used in an attack. This is especially important for data that will be displayed in the frontend to prevent cross-site scripting (XSS) attacks.
- **Data Escaping:** Data that is included in SQL queries is escaped to ensure that it is treated as data and not part of the SQL command. This prevents SQL injection attacks.
- **Password Hashing:** User passwords are hashed before being stored in the database. This means that even if the database is compromised, the actual passwords remain secure.
- **JWT for Sessions:** JSON Web Tokens (JWT) are used for managing user sessions. This provides a stateless and secure method for authenticating users on subsequent requests after login.

These measures help to ensure that the backend handles sensitive user data in a secure manner.

## Getting Started

This project was developed using MAMP. If you're using MAMP or a similar local server environment (like XAMPP or WampServer), the setup process should be straightforward.

### Prerequisites

- [PHP 7.4 or higher](https://www.php.net/downloads.php)
- [MySQL 5.7 or higher](https://dev.mysql.com/downloads/mysql/)
- [Composer](https://getcomposer.org/download/)
- [Firebase PHP-JWT library](https://github.com/firebase/php-jwt)

## Environment Variables

This project uses the `vlucas/phpdotenv` package to load environment variables from a `.env` file. This file should be located in the root directory of the project.

These environment variables are then accessible in the PHP code via the `$_ENV` superglobal array or the `getenv()` function.

For example, to get the database host, you would use `$_ENV['DB_HOST']` or `getenv('DB_HOST')`.

For the `JWT_KEY`, it's recommended to use a strong, random key. You can generate one using a tool like [RandomKeygen](https://randomkeygen.com/). Replace `myjwtkey` with the generated key.

### Installation

1. Clone the backend repository.
2. Install PHP dependencies: 

```bash
composer install
```

3. Create a .env file in the root of your project and configure your database connection:

```bash
DB_HOST=your-database-host
DB_PORT=your-database-port
DB_DATABASE=your-database-name
DB_USER=your-database-user
DB_PASSWORD=your-database-password
JWT_KEY=myjwtkey
```

4. Start the PHP development server:

```bash
php -S localhost:8888
```

## Database Setup

To set up the database for this project, you need to run the `create_daily_agenda_db.sql` script located in the `database` directory. This script will create the `daily_agenda` database and the `todos` and `user` tables.

Here are the steps to run the script using various tools:

#### MySQL Workbench
1. Open MySQL Workbench and connect to your MySQL server with your username and password.
2. Click on `File > Open SQL Script` and select the `create_daily_agenda_db.sql` script.
3. Click on the lightning bolt icon to execute the script.

#### phpMyAdmin
1. Open phpMyAdmin and log in with your username and password.
2. Click on the `Import` tab.
3. Click on `Choose File` and select the `create_daily_agenda_db.sql` script.
4. Click on `Go` to execute the script.

#### MySQL Command Line Tool
1. Open the MySQL command line tool and log in with your username and password.
2. Use the `source` command to execute the script:

```bash
source /path/to/create_daily_agenda_db.sql
```

## API Endpoints

### User Registration

- Endpoint: `POST /user_registration.php`
- Request body: JSON with `fullName`, `email`, and `password` fields.

Example request:

```json
{
  "fullName": "John Doe",
  "email": "john@example.com",
  "password": "password123"
}
```

Example response:

```json
{
  "message": "Registration successful."
}
```

### User Login

- Endpoint: `POST /user_login.php`
- Request body: JSON with `email`, and `password` fields.

Example request:

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

Example response:

```json
{
  "jwt": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOiIxIiwiaWF0IjoxNjI5MzI4Mzg0LCJleHAiOjE2MjkzMzE5ODR9.5Y9oY2WugJDkGs2dh17YpI5eCq_rSZt8cI6VZf6o5w4"
}
```

### Fetch Todos

- Endpoint: `GET /fetch_todos.php`
- Requires a valid JWT in the Authorization header.

Example response:

```json
{
  "todos": [
    {
      "taskId": "1",
      "completed": "0",
      "data": "Buy milk",
      "createdAt": "2021-08-18 12:00:00",
      "completedAt": null
    },
    {
      "taskId": "2",
      "completed": "1",
      "data": "Walk the dog",
      "createdAt": "2021-08-18 12:00:00",
      "completedAt": "2021-08-18 13:00:00"
    }
  ]
}
```

### Insert Todo

- Endpoint: `POST /insert_todos.php`
- Request body: JSON with completed and data fields.
- Requires a valid JWT in the Authorization header.

Example request:

```json
{
  "completed": "0",
  "data": "Buy bread"
}
```

Example response:

```json
{
  "message": "Todo inserted successfully."
}
```

### Update Todo

- Endpoint: `PUT /update_data-completed.php`
- Request body: JSON with `taskId`, `completed`, and `completedAt` fields.
- Requires a valid JWT in the Authorization header.

Example request:

```json
{
  "taskId": "1",
  "completed": "1",
  "completedAt": "2021-08-18 14:00:00"
}
```

Example response:

```json
{
  "message": "Todo updated successfully."
}
```

### Delete Todo

- Endpoint: `DELETE /delete_todo.php`
- Request body: JSON with `taskId` field.
- Requires a valid JWT in the Authorization header.

Example request:

```json
{
  "taskId": "1"
}
```

Example response:

```json
{
  "message": "Todo deleted successfully."
}
```

### Usage

This backend serves as the API endpoint for the Daily Agenda front end. Ensure the front end is configured to make requests to this backend URL.

### Technologies Used

The backend of Daily Agenda is built with PHP 7.4 and interacts with a MySQL 5.7 database. It uses JSON Web Tokens (JWT) for user authentication, with the Firebase PHP-JWT library version 5.2.0.

### Contact

For inquiries or questions, feel free to contact me at lindyo87@gmail.com. You can view the rest of my portfolio [here](https://www.lindyramirez.com).

If you find this project helpful, please consider giving it a star on GitHub:

[![GitHub stars](https://img.shields.io/github/stars/habanerocity/daily_agenda_backend.svg?style=social&label=Star)](https://github.com/habanerocity/daily_agenda_backend)

You can also check out the frontend repository for this project [here](https://github.com/habanerocity/dailyagenda).

# Online Shop API

This is a RESTful API for building a simple online shop. The API can be accessed at `https://api.baktiweb.my.id/`

## Specifications

-   Language: PHP
-   Framework: Laravel
-   Web Server: Nginx
-   Containerization: Docker
-   Core Libraries: MongoDB, Passport, OAuth 2.0, Redis, Cloudinary, Midtrans, RajaOngkir, Websockets, Scout, Meilisearch

## Features

-   Payment Gateway
-   Shipping Cost Checker
-   Realtime Notifications
-   Smart Search
-   Multilingual
-   Multiple Access Roles
-   Multi-database (SQL & NoSQL)

## Architecture

This application adopts the Repository Pattern architecture to separate business logic from the data storage layer. The application structure has the following components:

-   app: This directory contains the implementation of business logic, including models, controllers, services, and repositories.
-   config: This directory contains Laravel configuration files, such as database configuration, file system, and others.
-   database: This directory contains migrations and seeders for the database.
-   routes: This directory contains HTTP routing definitions for the application.
-   tests: This directory contains unit tests and feature tests to ensure code quality.

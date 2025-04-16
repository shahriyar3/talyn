# Talyn Gold Trading Platform

This project is a RESTful API for gold trading in the Talyn platform. It enables users to place buy and sell orders and matches them based on price compatibility.

## Features

- User authentication using Laravel Sanctum
- Buy and sell order placement for gold
- Order matching system based on price
- Transaction history tracking
- Tiered commission structure:
  - 2% for gold amounts ≤ 1g
  - 1.5% for gold amounts > 1g and ≤ 10g
  - 1% for gold amounts > 10g
  - Minimum commission: 50,000 Rials
  - Maximum commission: 5,000,000 Rials
- Random initial balance for new users:
  - Gold: Random amount between 1g and 20g
  - Cash: Random amount between 10 million and 500 million Rials
- RESTful API architecture
- API documentation with Swagger
- Containerized with Docker
- Caching with Redis

## Design Patterns

### Service Layer Pattern
The service layer contains the business logic and acts as an intermediate layer between controllers and repositories:
- **Separation of Concerns**: Controllers handle HTTP requests, services handle business logic.
- **Reusability**: Business logic can be reused across different parts of the application.
- **Transaction Management**: Services handle database transactions for financial operations.

### Observer Pattern
Used for event handling after order matching:
- **Loose Coupling**: Components can communicate without direct dependencies.
- **Event-Driven Architecture**: System responds to events rather than procedural calls.

## Installation

### Prerequisites
- Docker and Docker Compose
- Git

### Setup Steps

1. Clone the repository:
```bash
git clone https://github.com/yourusername/talyn.git
cd talyn
```

2. Start the Docker environment:
```bash
docker-compose up -d
```

3. Install dependencies:
```bash
docker-compose exec app composer install
```

4. Generate application key:
```bash
docker-compose exec app php artisan key:generate
```

5. Run migrations and seeders:
```bash
docker-compose exec app php artisan migrate --seed
```

6. Generate API documentation:
```bash
docker-compose exec app php artisan l5-swagger:generate
```

7. Access the application:
   - Web: http://localhost:8000
   - API Documentation: http://localhost:8000/api/doc

## API Endpoints

The API follows RESTful conventions with the following main endpoints:

- `POST /api/register` - Register a new user
- `POST /api/login` - Authenticate a user
- `GET /api/user` - Get authenticated user details
- `GET /api/user/balance` - Get user gold and cash balance
- `POST /api/orders` - Create a new buy/sell order
- `GET /api/orders` - List user's orders
- `DELETE /api/orders/{id}` - Cancel an order
- `GET /api/transactions` - Get user's transaction history

Detailed API documentation is available via Swagger UI after installation.


## Tests & Test Coverage

The project uses Pest for writing tests. Pest is a testing framework for PHP that focuses heavily on developer experience (DX), making tests more readable and easier to write.

### Test Coverage

Unit tests and feature tests are written to ensure the correct functionality of different components of the system:

- **Unit Tests**: Test isolated parts of the code (using Mocks and Stubs)
  - Rial and Toman conversion (`RialTomanConversionTest`)
  - Order actions (`CreateOrderActionTest`)
  - Currency helpers (`CurrencyHelperTest`)

- **Feature Tests**: End-to-end tests that verify API endpoints and complete system functionality
  - Order management (`OrderPestTest`)
  - User authentication (`UserPestTest`)
  - Transactions (`TransactionPestTest`)

To run the tests:

```bash
docker-compose exec app ./vendor/bin/pest
```

To view code coverage:

```bash
docker-compose exec app ./vendor/bin/pest --coverage
```

The project aims to achieve a code coverage above 90% for all core classes.

Format code with Laravel Pint:
```bash
docker-compose exec app composer format
```

## License

This project is licensed under the MIT License - see the LICENSE file for details.

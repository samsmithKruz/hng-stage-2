# HNG Internship Task: Backend Stage 2

This project is developed as part of the HNG Internship Task for backend stage 2. It implements a RESTful API using PHP, focusing on user authentication, organization management, and database migrations. The project utilizes JWT for authentication, PDO for database interactions, and follows the MVC architecture pattern.

## Features

- **User Authentication**: Register and login users securely using JWT authentication.
- **Organization Management**: Create, retrieve, and manage organizations.
- **Database Migrations**: Perform database migrations to create required tables.

## Requirements

- PHP 7.4+
- PostgreSQL database
- Composer (for dependency management)

## Installation

1. **Clone the repository**:

    ```bash
    git clone https://github.com/yourusername/yourproject.git
    cd yourproject
    ```

2. **Install dependencies**:

    ```bash
    composer install
    ```

3. **Set up environment variables**:

    Create a `.env` file in the root directory and configure your database and JWT secret:

    ```env
    DB_HOST=your_db_host
    DB_PORT=your_db_port
    DB_DATABASE=your_db_name
    DB_USERNAME=your_db_username
    DB_PASSWORD=your_db_password
    JWT_SECRET=your_jwt_secret
    JWT_EXP=1 # JWT expiration time in hours
    ```

## Usage

### Endpoints

- **Register a new user**:
  ```POST /api/register```

- **Login an existing user**:
  ```POST /api/login```

- **Create a new organization**:
  ```POST /api/organisations```

- **Get details of a specific organization**:
  ```GET /api/organisations/{orgId}```

- **Add users to an organization**:
  ```POST /api/organisations/{orgId}/users```

- **Retrieve all organizations a loggedin user belongs to**:
  ```GET /api/organisations```

- **Retrieve details of a specific user**:
  ```GET /api/users/{userId}```

- **Perform database migrations**:
  ```GET /api/migrate```

- **Drop database table migrations**:
  ```GET /api/migrate/drop/{tableName}```


## Directory Structure

- **Controllers/**: Contains controller classes for handling API requests.
- **Libraries/**: Contains utility classes like Database, Request, Response, and JWT handling.
- **Models/**: Contains model classes for interacting with the database.
- **Migrations/**: Contains migration scripts for database schema changes.

## Contributing

Contributions are welcome! Please fork the repository, make changes, and submit a pull request.


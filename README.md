<h1 align="center">Leave Management System</h1>
A modern, clean, production-ready **Leave Management System API** built with:
- Laravel 12
- JWT Authentication
- Role-Based Access Control (Employee | Manager | Admin)
- MySQL
- Full CRUD + Approve/Reject Workflow

## Features
- JWT Login / Logout / Refresh
- 3 Roles: Employee, Manager, Admin
- Create, Update, Cancel Leave Requests
- Managers can Approve/Reject with notes
- Automatic leave balance deduction
- Comments on leave requests
- `/api/users/me` endpoint
- Clean RESTful routes
  
## API Endpoints
| Method   | Endpoint                          | Description                        | Required Role         |
|---------|-----------------------------------|------------------------------------|------------------------|
| POST    | `/api/auth/login`                 | Login → returns JWT token          | Public                 |
| GET     | `/api/users/me`                   | Get current user                   | Authenticated          |
| GET     | `/api/users`                      | List all users                     | Admin                  |
| POST    | `/api/users`                      | Create new user                    | Admin                  |
| GET     | `/api/leaves`                     | List leaves (own/team)             | All                    |
| POST    | `/api/leaves`                     | Apply for leave                    | Employee+              |
| GET     | `/api/leaves/{id}`                | View leave details                 | Owner / Manager / Admin |
| PUT     | `/api/leaves/{id}`                | Update leave (pending only)        | Owner                  |
| PATCH   | `/api/leaves/{id}/approve`        | Approve leave                      | Manager / Admin        |
| PATCH   | `/api/leaves/{id}/reject`         | Reject leave with reason           | Manager / Admin        |
| GET     | `/api/leaves/{id}/comments`       | View comments                      | Involved users          |
| POST    | `/api/leaves/{id}/comments`      | Add comment                        | Involved users          |

## Setup
```bash
git clone https://github.com/siam/leave-management-system.git
cd leave-management-system
composer install
cp .env.example .env
php artisan key:generate
```

## Edit .env:
```bash
DB_DATABASE=leave_db
DB_USERNAME=root
DB_PASSWORD=
```
## Run Server
```bash
php artisan migrate --seed
php artisan serve
```

## Login credentials
- Admin: admin@company.com → 123456
- Manager: manager@company.com → 123456
- Employee: sifat@company.com → 123456

## Tech Stack
- Laravel 12
- MySQL
- tymon/jwt-auth
- Carbon for date handling

## Author
Siam Ahmed
Dhaka, Bangladesh

# TutorBloom

**TutorBloom** is a full-stack web application for discovering tutors, matching students with suitable instructors, managing tutor availability, and booking tutoring sessions.

The platform implements separate workflows for **students, tutors, and administrators**, combining server-side PHP, relational data management, role-based authentication, AJAX interactions, and common web security practices.

## Features

### Student

- Browse approved tutor profiles
- Search and filter tutors by subject, level, price, and lesson format
- View tutor experience, pricing, subjects, and availability
- Book available tutoring sessions
- Track existing bookings
- Complete a multi-step **Perfect Tutor Matcher**
- Receive tutor recommendations based on preferences and placement-test results

### Perfect Tutor Matcher

TutorBloom includes a multi-step matching workflow that collects information such as:

- subject or language
- learning objective
- budget
- preferred lesson format
- availability
- study frequency
- estimated current level

The application also includes an **A1–C1 placement test**. The resulting score is mapped to a CEFR level and combined with the student's preferences to pre-filter suitable tutors.

### Tutor

Tutors have their own role-specific dashboard where they can:

- create and edit their tutor profile
- specify teaching experience and pricing
- manage subjects and supported levels
- add and remove available time slots
- view incoming bookings
- accept or reject booking requests
- monitor available slots and booking statistics

Tutor profiles go through an administrator approval process before becoming available to students.

### Administrator

Administrators can:

- monitor users and tutor profiles
- review tutor applications
- approve or reject pending tutors
- inspect tutor information and subjects
- edit tutor data asynchronously

Tutor administration is implemented in two versions to demonstrate different client-side approaches:

- **Vanilla JavaScript + AJAX**
- **jQuery + AJAX**

Both versions dynamically retrieve and update tutor information without requiring full-page reloads.

## Security

The application implements several server-side security measures, including:

- CSRF protection for state-changing requests
- output escaping to reduce XSS risk
- prepared SQL statements
- role-based authorization
- secure session cookie configuration
- HTTPS enforcement outside localhost
- Content Security Policy (CSP)
- `X-Frame-Options`
- `X-Content-Type-Options`
- restrictive browser permissions policy
- validation and sanitization of user input
- controlled handling of uploaded profile files

## Tech Stack

| Area | Technologies |
|---|---|
| Backend | PHP |
| Frontend | HTML5, CSS3, JavaScript |
| Asynchronous UI | AJAX, jQuery |
| Database | MySQL |
| Database Access | PDO, MySQLi |
| Server | Apache |
| Authentication | PHP Sessions |
| Security | CSRF tokens, prepared statements, output escaping, security headers |

## Application Roles

TutorBloom uses role-based access control with three account types:

**Student** → searches for tutors, completes the matching process, and books lessons.

**Tutor** → manages a public teaching profile, availability, and booking requests.

**Administrator** → reviews tutor applications and manages tutor information.

Users are automatically redirected to the appropriate dashboard according to their authenticated role.

## Project Structure

```text
tutorbloom/
├── admin/          # Tutor review and administration
├── assets/         # CSS, JavaScript and images
├── config/         # Database configuration
├── data/           # Application data
├── database/       # Database-related resources
├── includes/       # Shared authentication, security and utility logic
├── private/        # Protected application resources
├── student/        # Student dashboard, tutor search and matching
├── tutor/          # Tutor dashboard, profile and availability management
├── database.sql    # Database initialization
├── index.php       # Application entry point
├── login.php       # Authentication
├── logout.php
└── register.php    # Account registration
```

## Core Workflow

```text
Registration / Login
        │
        ▼
 Role-based authentication
        │
 ┌──────┼───────────────┐
 ▼      ▼               ▼
Student Tutor           Admin
 │      │               │
 │      ├─ Profile      ├─ Review tutors
 │      ├─ Availability ├─ Approve / reject
 │      └─ Bookings     └─ AJAX management
 │
 ├─ Search tutors
 ├─ Perfect Tutor Matcher
 ├─ View profiles
 └─ Book sessions
```

## Database Model

The relational database stores the main entities required by the platform, including:

- users and roles
- tutor profiles
- subjects and tutor-subject relationships
- tutor availability
- lesson bookings
- authentication-related data

Relationships between these entities allow TutorBloom to connect students with approved tutors while maintaining separate permissions and workflows for each account type.

## Running Locally

### Requirements

- PHP
- Apache
- MySQL / MariaDB
- PDO MySQL extension
- a local development environment such as XAMPP, WAMP, or a standard LAMP stack

### Setup

1. Clone the repository:

```bash
git clone https://github.com/victor-moga30/tutorbloom.git
```

2. Move the project into your web server's document root.

For XAMPP:

```text
xampp/htdocs/tutorbloom
```

3. Create the application database in MySQL.

4. Import:

```text
database.sql
```

5. Configure the database connection inside the `config/` directory for your local MySQL installation.

6. Start Apache and MySQL.

7. Open the application through your local web server:

```text
http://localhost/tutorbloom/
```

## What This Project Demonstrates

TutorBloom was built as a complete web application covering both frontend and backend development. It demonstrates:

- server-side application development with PHP
- relational database design and SQL
- authentication and role-based authorization
- CRUD operations
- multi-step form workflows
- dynamic filtering and tutor matching
- booking and availability management
- asynchronous communication using AJAX
- DOM manipulation with JavaScript and jQuery
- session management
- practical web security techniques

## Author

**Victor Moga**

Computer Science student at Babeș-Bolyai University.

GitHub: `victor-moga30`

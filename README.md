<div align="center">

# Life Drop
<img height="200" alt="logo" src="https://github.com/user-attachments/assets/5183103d-a501-45f0-a9f8-f517cdded537" />

**LifeDrop is a Symfony web app to post urgent blood donation requests and let nearby donors respond by city and blood type.**

[![Last Commit](https://img.shields.io/github/last-commit/GhassenHammami/life-drop)](https://github.com/GhassenHammami/life-drop/commits/master)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-8892BF?logo=php)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-6.x-000000?logo=symfony)](https://symfony.com/)
[![Languages](https://img.shields.io/github/languages/count/GhassenHammami/life-drop)](https://github.com/GhassenHammami/life-drop)

### Built with the tools and technologies

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-8892BF?logo=php)](https://www.php.net/)  
[![Symfony](https://img.shields.io/badge/Symfony-6.x-000000?logo=symfony)](https://symfony.com/)  
[![Doctrine](https://img.shields.io/badge/Doctrine-ORM-7A9CC6?logo=doctrine)](https://www.doctrine-project.org/)  
[![Twig](https://img.shields.io/badge/Twig-3.0-8B8E93?logo=twig)](https://twig.symfony.com/)  
[![Composer](https://img.shields.io/badge/Composer-2.x-919191?logo=composer)](https://getcomposer.org/)  

</div>

## Table of Contents

- [Project Description](#project-description)
- [Tech Stack](#tech-stack)
- [Features](#features)
- [Screenshots](#screenshots)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Environment Setup](#environment-setup)
  - [Database Setup](#database-setup)
  - [Start Development Server](#start-development-server)
- [Usage](#usage)
  - [Common Flows](#common-flows)
  - [Inspect Routes](#inspect-routes)
- [License](#license)
- [Contributing](#contributing)
- [Acknowledgments](#acknowledgments)

## Project Description

LifeDrop helps people publish urgent blood donation requests and enables nearby donors to respond based on city and blood type. Core flows:
- Requesters publish requests with patient/hospital/urgency details.
- Donors browse and filter requests by city and blood type.
- Donors submit offers to donate; requesters review offers and accept or reject them.
- Both parties get status updates and can track items via "My Requests" and "My Offers" dashboards.

## Tech Stack

- PHP 8.1+
- Symfony 6.x
- Doctrine ORM
- Twig (server-rendered UI)
- Composer for dependency management

## Features

- Publish urgent blood donation requests (hospital, blood type, units needed, urgency, contact)
- Browse and filter open requests by city, blood type, and urgency
- Donor offers: donors submit intent to donate with contact/time
- Offer review: requesters accept or reject offers
- Status updates and notifications for accepted matches
- Dashboards: "My Requests" for requesters, "My Offers" for donors
- Role-based access (requester, donor), account profile with blood type and city

## Screenshots

<details>
  <summary>Click to view screenshots</summary>

  <p align="center">
    <a href="https://github.com/user-attachments/assets/17d109c7-e655-451e-9479-2b95f5351437">
      <img width="1920" height="911" alt="1" src="https://github.com/user-attachments/assets/17d109c7-e655-451e-9479-2b95f5351437" />
    </a>
    <a href="https://github.com/user-attachments/assets/544d8407-bea3-4f74-950c-e510f647dc55">
      <img width="1920" height="911" alt="2" src="https://github.com/user-attachments/assets/544d8407-bea3-4f74-950c-e510f647dc55" />
    </a>
    <a href="https://github.com/user-attachments/assets/9b9fa300-faa4-4611-98e1-0c93c38d54fb">
      <img width="1920" height="911" alt="3" src="https://github.com/user-attachments/assets/9b9fa300-faa4-4611-98e1-0c93c38d54fb" />
    </a>
  </p>

  <p align="center">
    <a href="https://github.com/user-attachments/assets/9b7baa34-1764-4283-ad55-854feaca7d43">
      <img width="1920" height="911" alt="4" src="https://github.com/user-attachments/assets/9b7baa34-1764-4283-ad55-854feaca7d43" />
    </a>
    <a href="https://github.com/user-attachments/assets/086487eb-8f09-4a45-a587-34b565b3a90b">
      <img width="1920" height="911" alt="5" src="https://github.com/user-attachments/assets/086487eb-8f09-4a45-a587-34b565b3a90b" />
    </a>
    <a href="https://github.com/user-attachments/assets/66d754e8-3e28-47a0-8735-469331d08597">
      <img width="1920" height="1657" alt="6" src="https://github.com/user-attachments/assets/66d754e8-3e28-47a0-8735-469331d08597" />
    </a>
  </p>

  <p align="center">
    <a href="https://github.com/user-attachments/assets/afba5e99-72d9-4520-92e6-c13b9eb06014">
      <img width="1920" height="911" alt="7" src="https://github.com/user-attachments/assets/afba5e99-72d9-4520-92e6-c13b9eb06014" />
    </a>
    <a href="https://github.com/user-attachments/assets/75443873-36a1-46ac-a216-f467b4e814d0">
      <img width="1920" height="911" alt="8" src="https://github.com/user-attachments/assets/75443873-36a1-46ac-a216-f467b4e814d0" />
    </a>
    <a href="https://github.com/user-attachments/assets/0b75b9d6-7438-4a7e-95b7-440cbe530fd5">
      <img width="1920" height="911" alt="9" src="https://github.com/user-attachments/assets/0b75b9d6-7438-4a7e-95b7-440cbe530fd5" />
    </a>
  </p>

</details>



## Getting Started

### Prerequisites

- PHP 8.1 or newer
- Composer 2.x
- (Optional) Symfony CLI: https://symfony.com/download
- A database supported by Doctrine (MySQL, PostgreSQL, or SQLite)
- Git

### Installation

1. Clone the repository
   ```bash
   git clone https://github.com/GhassenHammami/life-drop.git
   cd life-drop
   ```

2. Install PHP dependencies
   ```bash
   composer install
   ```

3. Install frontend deps
   ```bash
   npm install
   ```

### Environment Setup

1. Copy the example environment file and edit values
   ```bash
   cp .env .env.local
   ```
   Ensure at minimum:
   - APP_ENV=dev
   - APP_SECRET=your_app_secret
   - DATABASE_URL=`mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0`

2. Create the database (if not using SQLite)
   ```bash
   php bin/console doctrine:database:create
   ```

### Database Setup

1. Run migrations to create the schema
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

2. (Optional) Load fixtures (seed sample users, requests)
   ```bash
   php bin/console doctrine:fixtures:load
   ```

3. Inspect routes (helpful to learn available endpoints)
   ```bash
   php bin/console debug:router
   ```

### Start Development Server

- With Symfony CLI (recommended)
  ```bash
  symfony server:start
  ```

- Or using PHP built-in server
  ```bash
  php -S 127.0.0.1:8000 -t public
  ```

Open the URL shown by the Symfony CLI (default http://127.0.0.1:8000).

## Usage

- Register a user and complete your profile (include blood type and city).
- Requesters: Create a new donation request with hospital, required blood type, units, urgency level, and contact info.
- Donors: Browse requests, filter by city and blood type, click a request to submit an offer (proposed time/contact).
- Requesters: Review incoming offers and accept/reject; accepted offers mark the request as matched and notify the donor.
- Dashboards: Use "My Requests" to track status and "My Offers" to track offers you submitted.


Note: use php bin/console debug:router to find exact routes and adjust payloads to match entity fields.

### Inspect Routes
List all routes:
```bash
php bin/console debug:router
```

## License

This project is licensed under the [MIT License](LICENSE.md) — see the LICENSE file for details.

## Contributing

1. Fork the repository.
2. Create a feature branch: git checkout -b feature/awesome
3. Install dependencies and run migrations locally.
4. Run tests.
5. Open a Pull Request describing your changes.

## Acknowledgments

- Symfony and its community
- Doctrine ORM
- Contributors and maintainers of libraries used in this project

---
Built with ❤️ using Symfony and PHP.

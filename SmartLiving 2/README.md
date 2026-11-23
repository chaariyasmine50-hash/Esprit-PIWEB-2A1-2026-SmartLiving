# Smart Living – Smart City Platform for Tunisian Municipalities

## Overview
This project was developed as part of the **PIWEB – 2nd Year Engineering Program** at **Esprit School of Engineering** (Academic Year 2026–2027).

Smart Living is a web platform that connects citizens and their municipality. Citizens report urban problems, join green events, discuss in a community forum, earn green rewards, file complaints and support local campaigns with donations. Administrators manage the whole platform from a secured back office.

## Table of Contents
- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Security](#security)
- [Contributors](#contributors)
- [Academic Context](#academic-context)
- [Getting Started](#getting-started)
- [Contribution](#contribution)
- [License](#license)
- [Acknowledgments](#acknowledgments)

## Features

### User Management (shared module) – done
| Feature | Who | Action |
|---|---|---|
| Register (password hashed with bcrypt) | Visitor | `?action=register` |
| Log in / Log out (PHP sessions) | Everyone | `?action=login` / `?action=logout` |
| Personal space (front office) | User | `?action=dashboard` |
| Read / update my profile | User | `?action=profile` |
| Change my password | User | `?action=password` |
| Delete my account | User | `?action=account_delete` |
| Admin dashboard (statistics) | Admin | `?action=admin` |
| List, search and filter users | Admin | `?action=admin_users` |
| Create a user and choose his role | Admin | `?action=admin_user_create` |
| Update a user, change role, status and points | Admin | `?action=admin_user_edit&id=…` |
| Block / unblock an account | Admin | `?action=admin_user_toggle` |
| Delete a user | Admin | `?action=admin_user_delete` |

**Roles:** `user` (citizen → front office) and `admin` (back office). Every sensitive page checks the role on the server (`requireAuth()` / `requireRole('admin')`). A user who opens an admin URL gets **403 – Access denied**. A blocked account cannot log in and is logged out immediately.

### Modules (one per team member)
| Module | Entities |
|---|---|
| Urban Reporting | Incident, Task |
| Green Events | GreenEvent, EventParticipation |
| Forum | Post, Comment |
| Green Rewards | Reward, Redemption |
| Complaints | Reclamation, Response |
| Donations | Campaign, Donation |

## Tech Stack
### Frontend
- HTML5, CSS3
- JavaScript (client-side form validation)

### Backend
- PHP 8 (MVC architecture, PDO)
- MySQL / MariaDB (XAMPP)

## Architecture
The project follows the **MVC** architecture of the workshop *Sessions and PHP Security*:

```
SmartLiving/
├── config/
│   └── database.php          # PDO connection (Singleton)
├── controllers/
│   ├── AuthController.php    # register, login, logout, sessions, roles, CSRF
│   └── UserController.php    # user CRUD + profile
├── models/
│   └── UserModel.php         # attributes + getters/setters only
├── views/
│   ├── assets/               # css, js (validation.js), img
│   ├── partials/             # helpers (escaping, icons), alerts
│   ├── layout.php            # front office skin
│   ├── layout_admin.php      # back office skin
│   ├── login.php, register.php
│   ├── user_space.php, profile.php
│   ├── admin_space.php, admin_users.php, admin_user_form.php
│   └── error_403.php
├── docs/class_diagram.png
├── smartliving.sql           # database + demo accounts
└── index.php                 # single entry point (?action=...)
```

- **Model:** one piece of data, no SQL.
- **Controller:** all business logic, prepared SQL queries, validation, sessions, CSRF, roles.
- **View:** HTML only, every value escaped with `htmlspecialchars()`.

## Security
| Risk | Protection | Where |
|---|---|---|
| SQL injection | PDO prepared statements, `ATTR_EMULATE_PREPARES = false` | controllers, `config/database.php` |
| Plain-text passwords | `password_hash()` / `password_verify()` | `register()`, `login()`, `UserController` |
| XSS | `htmlspecialchars()` on output, input validation, CSP header | views, controllers, `index.php` |
| CSRF | Token per session, checked with `hash_equals()` on every form | `AuthController`, all forms |
| Session fixation | `session_regenerate_id(true)` after login | `login()` |
| Session theft | Cookie `HttpOnly` + `SameSite=Lax`, strict mode | `index.php` |
| Clickjacking | `X-Frame-Options: DENY` | `index.php` |
| Privilege escalation | Role forced to `user` at sign up, `requireRole()` on each admin page, role re-read from the database on every request | `register()`, `index.php` |
| Abandoned sessions | Expire after 30 minutes of inactivity | `index.php` |

**Input validation:** every form is checked twice: in JavaScript (`views/assets/js/validation.js`) and again in PHP (controllers).

## Contributors
| Name | Module |
|---|---|
| Student 1 | User management + Urban Reporting |
| Student 2 | Green Events |
| Student 3 | Forum |
| Student 4 | Green Rewards |
| Student 5 | Complaints |
| Student 6 | Donations |

## Academic Context
Developed at **Esprit School of Engineering – Tunisia**
PIWEB – 2A | 2026–2027

## Getting Started
### Installation
1. Clone the repository into the `htdocs` folder of XAMPP:
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs     # macOS
   # cd C:\xampp\htdocs                         # Windows
   git clone https://github.com/<organisation>/Esprit-PIWEB-<Classe>-2026-SmartLiving.git SmartLiving
   ```
2. Start **Apache** and **MySQL** in XAMPP.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`), click the **SQL** tab, paste the content of `smartliving.sql` and click **Go**. It creates the `smartliving` database, all the tables and two demo accounts.
4. If your MySQL has a password, change it in `config/database.php`.

### Usage
Open `http://localhost/SmartLiving/`

| Account | Email | Password |
|---|---|---|
| Administrator (back office) | `admin@smartliving.tn` | `Admin@1234` |
| Citizen (front office) | `user@smartliving.tn` | `User@1234` |

## Contribution
1. Always start with: `git pull origin main`
2. Work on your own branch: `git checkout -b <your-name>`
3. Commit often: `git commit -m "Add event list page"`
4. Push your branch: `git push origin <your-name>`
5. Open a **Pull Request** to `main`, then merge.
6. Update the task on the **GitHub Project Board** (To do → In progress → Done).

## License
This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

## Acknowledgments
We thank our supervisors at **Esprit School of Engineering** for their guidance during this project.

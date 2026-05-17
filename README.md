# Campus Connect — Event Management System

A full-featured college event management platform built with PHP and MySQL.

## Features
- Student, Organizer, and Admin dashboards
- Event creation with image upload
- QR-code based ticket verification
- Real-time stats via Server-Sent Events
- Email notifications & reminders
- Mobile responsive design

## Tech Stack
- **Backend:** PHP 8.x
- **Database:** MySQL
- **Frontend:** Tailwind CSS, Font Awesome

## Local Setup (XAMPP)

1. Clone the repo into `htdocs/`:
   ```
   git clone https://github.com/Theresa-Angel/Event-Management-System.git
   ```
2. Import the database:
   - Open phpMyAdmin
   - Create a database named `event_system`
   - Import `database/event.sql`

3. Configure the database in `config.php` (already set for XAMPP defaults).

4. Visit `http://localhost/Event-Management-System/`

## Environment Variables (for production)

| Variable | Description |
|---|---|
| `DB_HOST` | MySQL host |
| `DB_USER` | MySQL username |
| `DB_PASS` | MySQL password |
| `DB_NAME` | Database name |
| `SMTP_HOST` | SMTP server |
| `SMTP_USER` | SMTP email |
| `SMTP_PASS` | SMTP password |

## Deploy to Railway

[![Deploy on Railway](https://railway.app/button.svg)](https://railway.app/new/template)

1. Push this repo to GitHub
2. Go to [railway.app](https://railway.app) → New Project → Deploy from GitHub
3. Add a MySQL plugin
4. Set the environment variables above
5. Done — Railway gives you a live URL

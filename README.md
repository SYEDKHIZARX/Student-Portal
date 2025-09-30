# Student Portal (PHP + MySQL)

Role-based student portal (Admin, Teacher, Coordinator, Student) with enrollment, prerequisites check, transcript (PDF/CSV), and hardened security (CSRF, prepared statements, bcrypt, session guards).

## Quick Start (Local - XAMPP)
1. Import `student_portal_db.sql` into MySQL (db name: `student_portal_db`).
2. Configure `includes/db_connection.php` (dev defaults are fine on XAMPP).
3. Visit `/student_portal/index.php` and log in.

## Admin/Test Users
- Admin: `admin1 / admin123`
- Student: `S001 / pass123`
- Teacher: `teacher1 / teach123`

## Features
- Secure login (bcrypt + auto-upgrade from plaintext)
- CSRF protection & prepared statements
- Prerequisite check on enroll
- Transcript (GPA/CGPA) + CSV export
- Light, uniform Bootstrap UI

> Production: use HTTPS, error logging (display_errors=Off), least-priv DB user.

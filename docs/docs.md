# Hem & Ivy Auction Platform Documentation

## Overview

Hem & Ivy is a modern web platform for managing, showcasing, and bidding on curated fashion auctions. It provides a seamless experience for both end-users and administrators, focusing on usability, security, and reliability.

## Features

- User registration and authentication (including Google OAuth)
- Browse and search live auctions
- Place bids on active auctions
- Admin dashboard for managing users and auctions
- Responsive image upload and cropping for auction items
- Category and rarity tagging for items
- Real-time statistics and activity feed for admins

## Tech Stack

- PHP (backend)
- MySQL (database)
- HTML, CSS, JavaScript (frontend)
- Composer (dependency management)
- Google API Client (for OAuth and integrations)

## Project Structure

- `/admin` – Admin dashboard and management tools
- `/config` – Configuration files (DB, settings)
- `/core` – Core framework and routing
- `/public` – Public assets, entry point, uploads
- `/routes` – Route definitions
- `/vendor` – Composer dependencies
- `/views` – Page templates and partials
- `/docs` – Documentation

## Setup Instructions

1. **Clone the repository**
2. **Install dependencies:**
   ```bash
   composer install
   ```
3. **Configure database:**
   - Edit `config/config.php` with your MySQL credentials
   - Import the schema from `mysql.txt`
4. **Set up uploads directory:**
   - Ensure `/public/uploads` is writable by the web server
5. **Run the application:**
   - Point your web server to `/public/index.php` as the entry point

## Usage

- Visit `/` to see the home page and featured auctions
- `/auctions` lists all active auctions
- `/admin/dashboard` for admin statistics (admin login required)
- `/admin/auctions` to manage auction items

## Security Notes

- User sessions are protected and roles are checked for admin routes
- All user input is validated and sanitized
- File uploads are restricted to images and validated on the server

## Contribution

Feel free to open issues or submit pull requests for improvements or bug fixes.

---

For more details, see the README.md or contact the project maintainer.

# Complete Email Management Solution

This is an **opinionated email management solution** with powerful features for modern Laravel applications:

## ✨ Features

- ✅ **A/B Testing** – Easily test multiple email variants of same email
- 📤 **Track Outgoing Emails** – Monitor every email sent from your system  
- 📈 **Email Open & Click Tracking** – Know when your emails are opened and visited  
- 🛡️ **Spam Protection** – Prevent abuse with built-in safeguards  
- 🧩 **Filament Plugin Included** – Seamlessly integrates with your Filament admin panel  
- 👀 **Preview Emails** – Visual preview of all your email templates  
- 📰 **Newsletter Support** – Manage and send newsletters with ease

## Installation

- `composer require prajwal89/email-management`
- `php artisan vendor:publish --tag=email-management-views`
- `./vendor/prajwal89/email-management/resources/views/**/*.blade.php, add this to your filaments tailwind.config.js`
- `php artisan make:queue-table`
- `php artisan migrate`


## Core Concepts

- Email Event
   Email events are events that are automatically triggered by app eg. user registration

- Email Campaign
   Manually run email campaigns e.g notifing new feature

### Usage

- run command to create a email event
  
  ```bash
  php artisan make:email-event
  ```
  
- follow the instructions

## Campaign Emails

These events emails are manually triggered like marketing emails eg. 15% off on black friday sell

# Complete Email Management Solution for Laravel Apps

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
- `./vendor/prajwal89/email-management/resources/views/**/*.blade.php, add this to your filaments tailwind.config.js`
- `php artisan make:queue-table`
- `php artisan migrate`
- To watch incoming emails `php artisan imap:watch default --with=flags,headers,body` This is required if you are using Cold emailing feature or it will send all follow up emails despite of user reply. also it should be restarted like queue workers to load latest app code

## Core Concepts

- Email Event
   Email events are events that are automatically triggered by app eg. user registration

- Email Campaign
   Manually run email campaigns e.g notifying new feature

### Usage

- run command to create a email event
  
  ```bash
  php artisan make:email-event
  ```
  
- follow the instructions

## Campaign Emails

These events emails are manually triggered like marketing emails eg. 15% off on black friday sell

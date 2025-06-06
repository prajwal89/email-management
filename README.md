# Email ManageMent Module

## Installation

- `composer require prajwal89/email-management`
- `php artisan vendor:publish --tag=email-management-views`

## Core Concepts

- Email Event

   Email events are events that are automatically triggered by app eg. user registration

- Email Campaign
   Manually run email campaigns e.g notifing new feature
  

Email events are events that are automatically triggered by app eg. user registration

### Usage

- run command to create a email event
  ```bash
  php artisan make:email-event
  ```
  
- follow the instructions

## Campaign Emails

These events emails are manually triggered like marketing emails eg. 15% off on black friday sell


## Features

- Record each outgoing email
- Track if email is opened by user (via tracking pixel)
- Record traffic generated form each email
- Can preview each email that will be sent
- Can preview sent emails
- Email campaign support (record campaign progress and results)
- Adds unsubscribe link to each email with list unsubscribe header for easy unsbscribtion
- Handles unsbscribtion
- Spam emails sending protection


## Limitations

- Does not track emails with Cc and Bcc
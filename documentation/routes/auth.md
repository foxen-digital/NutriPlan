# Documentation: auth.php

Original file: `routes/auth.php`

# auth.php Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [Routes Overview](#routes-overview)
   - [Guest Routes](#guest-routes)
   - [Authenticated Routes](#authenticated-routes)
3. [Detailed Route Documentation](#detailed-route-documentation)
   - [RegisteredUserController](#registeredusercontroller)
   - [AuthenticatedSessionController](#authenticatedsessioncontroller)
   - [PasswordResetLinkController](#passwordresetlinkcontroller)
   - [NewPasswordController](#newpasswordcontroller)
   - [EmailVerificationPromptController](#emailverificationpromptcontroller)
   - [VerifyEmailController](#verifyemailcontroller)
   - [EmailVerificationNotificationController](#emailverificationnotificationcontroller)
   - [ConfirmablePasswordController](#confirmablepasswordcontroller)
  
---

## Introduction

The `auth.php` file is a routing configuration for authentication-related functionalities within the NutriPlan PHP application. It defines which HTTP routes are accessible to users based on their authentication status, organizing both guest and authenticated users' operations concerning registration, login, password reset, and email verification.

---

## Routes Overview

### Guest Routes
These routes are accessible to users who are not authenticated. They primarily handle user registration, login, and password recovery.

### Authenticated Routes
These routes are available to users who are logged in, providing functionalities like email verification, password confirmation, and logout.

---

## Detailed Route Documentation

### RegisteredUserController
- **Routes:**
  - `GET register` — Displays the registration form
  - `POST register` — Handles new user registration
  
- **Purpose**: 
  The `RegisteredUserController` manages user registration logic.
  
- **Methods**:
  - `create()`: Shows the registration form.
  - `store()`: Processes the registration data and creates a new user.

### AuthenticatedSessionController
- **Routes:**
  - `GET login` — Displays the login form
  - `POST login` — Authenticates the user
  - `POST logout` — Logs the user out

- **Purpose**: 
  The `AuthenticatedSessionController` handles user sessions, including logging in and out.

- **Methods**:
  - `create()`: Shows the login form.
  - `store()`: Authenticates user credentials and starts a session.
  - `destroy()`: Ends the user session.

### PasswordResetLinkController
- **Routes:**
  - `GET forgot-password` — Displays the password reset request form
  - `POST forgot-password` — Sends a password reset link to the user’s email

- **Purpose**: 
  This controller manages password reset requests.

- **Methods**:
  - `create()`: Shows the password reset request form.
  - `store()`: Sends a password reset link via email.

### NewPasswordController
- **Routes:**
  - `GET reset-password/{token}` — Displays the form to create a new password
  - `POST reset-password` — Updates the password in the database

- **Purpose**: 
  This controller manages the process of resetting a user's password using a token.

- **Methods**:
  - `create()`: Shows the new password form, validating the reset token.
  - `store()`: Updates the user’s password in the database.

### EmailVerificationPromptController
- **Routes:**
  - `GET verify-email` — Displays an email verification prompt

- **Purpose**: 
  This controller prompts users to verify their email addresses after registration.

### VerifyEmailController
- **Routes:**
  - `GET verify-email/{id}/{hash}` — Confirms the user's email address

- **Purpose**: 
  The `VerifyEmailController` verifies an email based on user ID and hash.

- **Middleware**: 
  - `signed`: Requires a signed URL.
  - `throttle:6,1`: Rate limits the verification request.

### EmailVerificationNotificationController
- **Routes:**
  - `POST email/verification-notification` — Resends the email verification notification

- **Purpose**: 
  This controller handles the request to resend the email verification notification.

- **Middleware**: 
  - `throttle:6,1`: Rate limits the number of notification requests.

### ConfirmablePasswordController
- **Routes:**
  - `GET confirm-password` — Displays the password confirmation form
  - `POST confirm-password` — Confirms the user's password

- **Purpose**: 
  This controller manages password confirmation for sensitive actions.

- **Methods**:
  - `show()`: Shows the password confirmation form.
  - `store()`: Verifies the password against the authenticated user's.

---

This documentation outlines the authentication routes and their associated controllers in the NutriPlan application. Each route is effectively organized into guest and authenticated groups, ensuring the application adheres to security best practices regarding user access. The detailed descriptions of controllers and methods provide a comprehensive understanding of the authentication processes implemented within the system.
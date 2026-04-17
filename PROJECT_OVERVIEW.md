# Smart Bus Portal
**Project Documentation & Overview**

## 1. Introduction
The **Smart Bus Portal** is a web-based digital ticketing system designed to modernize public transit. It replaces traditional paper tickets with a secure, environmentally friendly digital pass system. Commuters can purchase daily, monthly, or yearly passes online, manage their wallet, and board buses using a digital verification process. Administrators can manage users, bus routes, and broadcast notifications.

---

## 2. Core Features

### 👤 User Portal (Commuter)
1.  **Secure Authentication**:
    *   User registration with email verification and welcome emails.
    *   Secure login with hashed passwords.
2.  **Dashboard**:
    *   Real-time view of Pass Status (Active/Expired).
    *   Daily travel usage tracking (limits per day).
    *   Countdown timer for daily limit reset.
3.  **Digital Wallet & Payments**:
    *   Integrated **Razorpay Gateway** for secure transactions.
    *   **Buy Pass**: Purchase monthly (₹1) or yearly (₹2) passes (Test amounts).
    *   **Wallet Balance**: View current balance and transaction history.
    *   **Email Receipts**: Automatic payment confirmation emails with transaction details.
4.  **Boarding System**:
    *   **Check Pass**: Users select a bus code (based on district) to validate their pass.
    *   **Anti-Fraud**: Validates pass expiry and daily usage limits before allowing boarding.

### 🛡️ Admin Portal (Administrator)
1.  **Analytics Dashboard**:
    *   Live stats: Total Users, Active Passes, Total Revenue.
    *   Interactive Chart: User registration trends over the last 7 days.
2.  **User Management**:
    *   View all registered users with their details (Mobile, City, Email).
    *   Search functionality.
    *   **Remove User**: Ability to delete user accounts.
3.  **Bus Network Management**:
    *   **Manage Bus Codes**: Add unique codes for buses mapped to specific Districts (e.g., Madurai, Chennai).
    *   **Remove Codes**: Delete obsolete bus codes.
4.  **Broadcast System**:
    *   **Notifications**: Publish marquee notifications that appear on the top of the main website for all users.

---

## 3. Technology Stack

*   **Frontend**:
    *   **HTML5 / CSS3**: Custom "Glassmorphism" UI design for a premium dark-mode aesthetic.
    *   **JavaScript**: Dynamic interactions, Razorpay integration, Charts.js for analytics.
*   **Backend**:
    *   **Core PHP**: Native PHP 8.x for server-side logic (No heavy frameworks used).
    *   **PHPMailer**: Library for sending SMTP emails (Welcome & Payment receipts).
*   **Database**:
    *   **MySQL**: Relational database for storing users, wallets, passes, and transactions.
*   **Integrations**:
    *   **Razorpay**: Payment Gateway for handling credit card, UPI, and netbanking transactions.
    *   **Google Fonts**: "Outfit" typeface for modern typography.
    *   **FontAwesome**: Icons for UI elements.

---

## 4. Database Schema

The system uses `bus_portal` database with the following key tables:

*   `users`: Stores user info (username, email, password, role, city).
*   `wallet`: Tracks user balance, pass start date, and expiry date.
*   `transactions`: Logs Razorpay payment IDs and amounts.
*   `bus_codes`: Stores unique bus identifiers and their Districts.
*   `notifications`: Stores active site-wide alert messages.
*   `passes`: (Legacy/History) Stores individual pass usage records.

---

## 5. Setup & Installation

1.  **Server Requirements**: XAMPP (Apache + MySQL), PHP 8.0+.
2.  **Files**: Place the `bus_portal` folder inside `htdocs`.
3.  **Database**:
    *   Open `localhost/phpmyadmin`.
    *   Create database `bus_portal`.
    *   Import `database.sql`.
4.  **Configuration**:
    *   Update `core/config.php` with your Razorpay Key and Secret.
    *   Update `core/config.php` with your SMTP Email credentials.
5.  **Access**:
    *   **Home**: `http://localhost/bus_portal`
    *   **Admin**: Login with `admin` / `admin123`.

---

## 6. How to Use

### For Users
1.  **Register**: Create an account at `/auth/register.php`. Select your district.
2.  **Buy Pass**: Go to "Buy Pass", select Monthly or Yearly plan. Pay via Razorpay.
3.  **Board Bus**: Go to "Board Bus", enter the Bus Code displayed on the bus (e.g., MDU100). If valid, you get a "Access Granted" screen.

### For Admins
1.  **Login**: Use the main login page with admin credentials.
2.  **Add Bus Codes**: Go to "Bus Codes" in the sidebar, add new codes for different Tamil Nadu districts.
3.  **Send Alerts**: Go to "Notifications" to post updates about bus timings or holidays.

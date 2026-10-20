# PadBro - Apps to find and book padel court [Laravel, Filament, Flutter]

# PRD — PadBro

**Product:** PadBro

**Type:** Vertical SaaS + Consumer Booking App

**Domain:** Sports Facility Management (Padel Courts)

---

## 1. Product Overview

PadBro is a vertical software platform designed to help padel court operators manage court inventory, bookings, and customer transactions while providing players with a simple mobile experience to discover and book padel courts.

The product consists of:

- **B2B Web Application** for padel court managers
- **B2C Mobile Application** for customers (players)

PadBro targets padel court businesses that currently rely on manual booking processes (WhatsApp, phone calls, spreadsheets) and lack centralized operational control.

---

## 2. Problem Statement

Padel court operators face operational inefficiencies due to manual booking workflows:

- Difficulty tracking real-time availability
- Double bookings and booking conflicts
- Manual payment confirmation
- No structured customer database
- Limited visibility into revenue and utilization

Customers face friction when booking courts:

- Unclear availability
- Slow confirmation
- Inconsistent booking experience
- Lack of trust in payment and reservation status

---

## 3. Goals & Success Metrics

### Business Goals

- Enable padel court operators to manage bookings digitally
- Reduce booking errors and manual coordination
- Centralize customer and transaction data
- Create a scalable foundation for future monetization

### Success Metrics

- Number of active courts onboarded
- Booking completion rate
- Reduction in booking conflicts
- Monthly active managers
- Monthly booking volume

---

## 4. User Roles

### 4.1 Manager (Court Owner / Operator)

Primary user responsible for managing padel court operations.

### 4.2 Customer (Player)

End user who discovers and books padel courts.

---

## 5. Functional Requirements

### 5.1 Manager Features

### Authentication

- Login via email and password

### User Management

- Create, read, update, delete users
- Automatically assign `customer` role
- User fields:
    - Name
    - Email
    - Password
    - Gender
    - Photo
    - Phone number

### Master Data Management

- CRUD cities
- CRUD court categories:
    - Indoor
    - Outdoor

### Court Management

- CRUD courts with the following fields:
    - Name
    - City ID
    - Category ID
    - material
    - Price per hour
    - about
    - Photos (JSON)
    - Features (JSON)
    - Thumbnail image
    - Address
    - phone
    - Status: `draft`, `active`

### Booking Management

- View all court bookings
- Booking data fields:
    - Court ID
    - Booking date
    - start at
    - end at
    - Total hours
    - User ID
    - Price
    - Tax amount
    - Grand total
    - Status: `pending`, `paid`, `failed`
    - Proof of payment (image)

### Booking Approval Flow

- Review uploaded payment proof
- Approve or reject booking
- Status update reflected to customer

---

### 5.2 Customer Features

### Authentication

- Sign up
- Login

### Discovery

- Browse latest courts
- Browse courts by city
- Filter courts by:
    - Indoor / Outdoor
    - Grass / Cement

### Court Details

- View court information
- View photos, features, pricing

### Booking Flow

1. Click **Book Now**
2. View available dates and time slots
3. Select date and number of hours
4. Proceed to checkout
5. Review booking summary
6. Upload proof of payment
7. Submit booking

### Booking Locking Logic

- Selected date and time slot are locked immediately after checkout initiation
- Other customers cannot book the same slot while payment is pending

### Booking Status

- View booking status:
    - Pending approval
    - Approved (paid)
    - Rejected / failed

---

## 6. Non-Functional Requirements

### Performance

- Real-time availability updates
- Booking lock must prevent race conditions

### Scalability

- Support multiple courts across multiple cities
- Designed for future multi-location operators

### Security

- Role-based access control
- Secure file upload for payment proof

### Reliability

- Booking and payment state must be consistent
- No double-booking under concurrent usage

---

## 7. Tech Stack

### Backend

- Laravel 12
- Filament 4 (Admin / Manager Dashboard)

### Mobile

- Flutter (iOS & Android)

### Infrastructure

- API-first architecture
- Email notifications via Mailtrap (development phase)
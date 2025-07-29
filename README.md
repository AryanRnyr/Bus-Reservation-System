# 🚌 RN Bus Reservation System

The **RN Bus Reservation System** is a complete web-based ticket booking system developed for a single bus company — RN Bus Yatayat Pvt. Ltd. It allows passengers to search for buses, select seats, make payments online (via eSewa or Khalti), and receive their tickets via email in PDF format. The admin panel provides full control over buses, routes, schedules, and reservations.

---

## 🚀 Features

### 🔸 User Side
- Search for available buses by route and date
- Live seat layout display and selection
- Auto-fare calculation based on seat selection
- Temporary seat hold for 5 minutes during booking
- Passenger details form with countdown timer
- Integration with **eSewa** and **Khalti** payment gateways
- Email confirmation with downloadable **PDF ticket**
- Unique ticket number generation for each booking

### 🔹 Admin Side
- Add/edit/delete buses, locations, and schedules
- Manage seat availability and monitor bookings
- View and manage cancellation requests
- Control panel for orders, users, and routes
- Basic content management (e.g., news/updates)

---

## 🏗️ System Architecture

This project follows a **Three-Tier Architecture**:

1. **Presentation Layer**: HTML, CSS, JavaScript  
2. **Business Logic Layer**: PHP (core logic, validations, session management)  
3. **Data Layer**: MySQL database

---

## 🛠️ Technologies Used

- **Frontend**: HTML, CSS, JavaScript (Vanilla)
- **Backend**: PHP (no frameworks)
- **Database**: MySQL
- **Design Tools**: Figma, Draw.io, Google Fonts
- **Dev Tools**: Visual Studio Code
- **Payment Gateways**: eSewa, Khalti
- **Email Service**: PHPMailer (with PDF attachment via TCPDF)

---

## 📁 Folder Structure (Simplified)

/RNBus
│
├── index.php
├── login.php / register.php
├── bus_details.php
├── confirm_booking.php / complete_booking.php
├── email/
│ ├── send_ticket.php
│ └── ticket_template.pdf.php
├── admin/
│ ├── manage_buses.php
│ ├── manage_schedule.php
│ └── ...
├── assets/
│ ├── css/
│ ├── js/
│ └── images/
└── db/
└── connection.php


---

## 🧾 Database Tables

- `users`
- `bus`
- `location`
- `schedule_list`
- `seat_reservation`
- `orders`
- `cancel_request`
- `news`

---

## 💡 Known Limitations

- Supports only a single bus company (no multi-vendor capability)
- Seat layouts are fixed and not dynamically generated per bus
- Lacks advanced reporting/analytics features

---

## 📧 Email Integration

After successful booking and payment, the system:
- Generates a **PDF ticket**
- Sends it to the passenger's registered email using **PHPMailer**

---

## 💳 Payment Gateways

This system is integrated with:
- [x] **eSewa**
- [x] **Khalti**

Users are redirected to the gateway during payment. Upon success, the seat is confirmed; if canceled, the seat is released automatically after timeout or manual cancel.

---

## 📜 License

This project is strictly for **experience and idea reference only**.  
**Do not use** it for academic submission, commercial deployment, or any real-world application.

---

## 👨‍💻 Author

**Aryan Rauniyar**  
Bachelor in Computer Applications (BCA)  
National College (NIST)

---

> Thank you for checking out this project! If you like it, consider starring the repo or using it for inspiration only.


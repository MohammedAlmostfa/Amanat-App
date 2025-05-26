# 📁 Sharjah Office - User, Invoice & Debt Management System

A complete management system designed for service offices. This project simplifies the process of managing **users**, **invoices**, and **outstanding debts** with a clean interface and reliable backend logic.

---

## ⚙️ Key Features

-   👤 **User Management**

    -   Add, edit, delete users
    -   Archive and retrieve user data
    -   Track user activity

-   🧾 **Invoice Management**

    -   Generate and print invoices
    -   Filter by date or user
    -   Track payment status

-   💰 **Debt Management**

    -   Register user debts
    -   Send reminders for unpaid debts
    -   Generate monthly reports

-   🔐 **Authentication**
    -   Secure login using password
    -   Single main account for office use
    -   Ability to change/reset the password

---

## 🧰 Technologies Used

-   **Backend:** Laravel
-   **Frontend:** Flutter
-   **Database:** MySQL

---

### 🚀 Steps to Run the Project

1. **Clone the Repository**:

    ```sh
    git clone https://github.com/MohammedAlmostfa/Debts-App
    ```

2. **Navigate to the Project Directory**:

    ```sh
    cd Debts-App
    ```

3. **Install Backend Dependencies**:

    ```sh
    composer install
    ```

4. **Create Environment File**:

    ```sh
    cp .env.example .env
    ```

5. **Update `.env`** with your MySQL database settings.

6. **Run Migrations**:

    ```sh
    php artisan migrate
    ```

7. **Seed the Database (optional)**:

    ```sh
    php artisan db:seed
    ```

8. **Serve the Application**:

    ```sh
    php artisan serve
    ```

---

### 📌 Notes

-   Login is required to access the dashboard.
-   Only one main office user is allowed.
-   Password change is available after logging in.
-   Use Postman or an HTTP client for API testing.
-   Follow code standards and validation rules in the request files.

---

Thank you for using our system!  
**— Devnest Team**

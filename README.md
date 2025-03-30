# 🚀 Job Management System 📝

Welcome to the **Job Management System** repository! This system allows users to browse and apply for job listings, while providing administrators with the ability to manage job postings, users, and system settings. 🎯

---

## ✨ Features ✨

- 🔐 **Admin Login & Verification**: Admins can log in to manage jobs, users, and system settings securely.
- 💼 **Job Listings**: Users can browse available jobs and apply for positions directly through the platform.
- 📋 **Admin Management**: Admins can add, update, and delete job listings and manage user information.
- ✍️ **User Authentication**: Users can sign up, log in, and track their job applications.
- ⚠️ **Error Handling**: Built-in error messages improve the user experience when things don't go as planned.

---

## 📦 Requirements 📦

- 🖥 **PHP 8.2.12** or higher
- 🌐 **HTML5 & CSS**
- 🛠 **XAMPP** (or any other local server setup with PHP and MySQL)
- 🗄 **MySQL Database**

---

## 🎥 Video Demo 🎥

Check out a demo of the Job Management System in action! 🎬

[![🔗 Watch the Video Demo](https://img.youtube.com/vi/_bACqgyiWXw/maxresdefault.jpg)](https://youtu.be/_bACqgyiWXw)

---

## 🛠️ Installation 🛠️

### 1️⃣ Clone the repository

```bash
 git clone https://github.com/your-username/job-management-system.git
```

### 2️⃣ Set up XAMPP

- Download and install XAMPP if you haven't already.
- Launch XAMPP and start the **Apache (for PHP)** and **MySQL** services.

### 3️⃣ Configure the database 🗄

- Open your browser and navigate to **phpMyAdmin** (usually http://localhost/phpmyadmin).
- Create a new database, e.g., **job_management_system**.
- Import the necessary tables or SQL file if provided.

### 4️⃣ Update the database connection ⚙️

- Open the **database configuration file** (e.g., `config.php` or `db_connection.php`).
- Ensure that the database details match your phpMyAdmin setup.

Example:

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "job_management_system";
```

### 5️⃣ Move the project to `htdocs` 📂

Move the entire project folder into the `htdocs` directory inside your XAMPP installation directory:

```bash
C:\xampp\htdocs\job-management-system
```

### 6️⃣ Run the project 🚀

- Open your browser and go to: [http://localhost/job-management-system](http://localhost/job-management-system)

---

## 🏃‍♂️ Usage 🏃‍♂️

### For Admins 👨‍💻
- Log in with your admin credentials to access the **admin dashboard**.
- Manage **job listings** and **user data** from the management panel 🛠️.

### For Users 👩‍💼
- Register and log in to browse available job listings.
- Apply for jobs directly through the system 📄.

---

## 👥 Team Members 👥

- **Thinh Ndang** (Developer) - Worked on **EOI database table**, **EOI procession** and **Management** 💻.
- **Phuong Nguyen** (Leader) - Manage **Project File Tree** and **Group Timetable**📅.
- **Tung Nguyen** (Developer) - Worked on **Job database table** and **Job description displaying** 💼.
- **Minh Nguyen** (Developer) - Worked on **Database connection** and **Presentation** 💼.
---

## 🤝 Contributing 🤝

We welcome contributions! 🌱

If you want to help improve the project:

1. Fork the repo 🍴.
2. Submit a pull request 📩.
3. Follow the coding style used in the project and document new features 📝.

---

## 📜 License 📜

This project is **open-source** and available under the **MIT License**. Feel free to contribute and make it your own! 🙌


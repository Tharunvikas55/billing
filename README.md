🎓 Billing System
This application is designed to manage tuition billing across multiple branches with distinct user roles. It offers an intuitive interface for both Master and Admin users to efficiently manage subjects, fees, invoices, and students.

👥 User Roles
1. 🛡️ Master
Single Master: The Master user oversees all branches.
Key Responsibilities:
➕ Add new Admins to each branch.
📚 Add new subjects and set taxes and fees for each subject by branch.
🏢 Add new branches.
📊 View invoice reports for all branches on the dashboard.
2. 🏫 Admin
Separate Admins for Each Branch: Each branch is managed by its respective Admin.
Key Responsibilities:
📝 Add students to the branch.
🧾 Generate, pay dues, and reverse invoices.
👁️ View branch-specific invoice reports.
🚀 Project Setup and Installation
Follow these steps to set up the Tuition Billing Management application on your local machine:

Step 1: ⬇️ Download and Extract the Project
Download the project and extract it to the folder: C:\xampp\htdocs.
Step 2: ⚙️ Run XAMPP Server
Open XAMPP and start both Apache and MySQL services.
Step 3: 🛠️ Create Database
Open your browser and navigate to http://localhost/phpmyadmin.
Create a new database with the name tuitiondata.
Step 4: 📥 Import the Database
After creating the database, click on the "Import" tab in phpMyAdmin.
Choose the tuitiondata.sql file from the tuitionmanage folder and import it.
Step 5: 🌐 Access the Application
Open your browser and go to the login page at http://localhost/tuitionmanage/master_login.php.
Log in using the provided credentials to access the application.
🧾 Exporting Invoice Reports as PDF
To enable PDF export functionality for invoice reports:

📦 Download the dompdf library from the following link:
dompdf-2.0.7.zip
🗂️ Extract the downloaded dompdf folder and add it to your project directory.

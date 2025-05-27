![signup](https://github.com/user-attachments/assets/87fd1775-f993-418b-a041-3f4f8e3ef0ad)
![settings](https://github.com/user-attachments/assets/4e4c9315-d5b5-4d1e-95d7-2785e2a4d7b6)
![manage teachers](https://github.com/user-attachments/assets/34da8022-3e40-460a-bfee-c1e30b4b9915)
![manage learners](https://github.com/user-attachments/assets/63dd9705-1faa-4a45-9725-f54bc2301548)
![manage books](https://github.com/user-attachments/assets/060561d4-4bf5-400a-ab02-394ab493168c)
![login](https://github.com/user-attachments/assets/40f7b78b-48c3-49e5-8a2b-9aa1bdb0b4bb)
![dashboard](https://github.com/user-attachments/assets/462c0ba2-a639-421b-8405-84418aa5c66c)
![borrow](https://github.com/user-attachments/assets/4f89e7ea-5330-4928-a644-df2d3ed47885)
![analytics](https://github.com/user-attachments/assets/1c98ef6d-a8bf-4a27-a0c0-79129a0c48b8)

# Pre-School Management System

Welcome to the Pre School Management System! This is a simple yet powerful web application designed to help administrators manage learners, teachers, books, and borrowing records with ease.

## What This Project Is About

This system was built to make running a preschool smoother by providing an easy-to-use interface for managing all the important data. Whether it's keeping track of students, teachers, or the books they borrow, this system has you covered.

## What You'll Find Here

- **Admin Dashboard:** A clean and informative dashboard that shows you key stats like how many learners, teachers, and books you have, plus borrowing activity.
- **Learner Management:** Add, update, or remove learners, complete with profile pictures and detailed info.
- **Teacher Management:** Manage teacher profiles, including their contact info, specialization, and photos.
- **Book and Borrow Records:** Keep track of books and who has borrowed them, with options to mark returns.
- **Analytics:** Visual charts to help you understand your data better, powered by Chart.js.
- **Secure Login:** Only authorized admins can access the system.

## Technologies Used

- PHP for the backend logic
- PostgreSQL as the database
- Bootstrap 5 for responsive and modern UI
- Chart.js for beautiful data visualizations
- Font Awesome for icons

## How to Get Started

1. Make sure you have PHP and PostgreSQL installed on your machine, postgresql path while installing (c:\xampp\postgresql\...) not (c:\programfiles...).
   make sure to uncomment postgresql extenstions in xampp>php>php confic
2. Set up the PostgreSQL database named `pre_school`, copy the schemas in db.txt folder and paste to you database. Save changes
3. Update the database connection details in `config.php` if needed.
4. Place the project files in your web server directory (like XAMPP's `htdocs`).
5. Open your browser and go to the admin dashboard (e.g., `http://localhost/pre-school-system/admin_dashboard.php`).
6. Log in with your admin credentials and start managing your preschool! signup and create admin account in signup.php. The database is empty so start kicking by adding learners, teachers and books. Have fun!


## A Few Notes

- Images uploaded for learners, teachers, and books are stored in the `uploads/` folder.
- The system uses prepared statements to keep your data safe.
- The interface is mobile-friendly and easy to navigate.

## What Could Be Next?

- Adding different user roles and permissions for more control.
- Enhancing security with password hashing and better authentication.
- Exporting and importing data for backups or reports.
- Notifications for overdue books or important events.
- UI improvements with modern JavaScript frameworks.

Thanks for checking out the Pre School Management System! We hope it makes managing your preschool a breeze.

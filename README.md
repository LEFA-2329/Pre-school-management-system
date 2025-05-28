![signup](https://github.com/user-attachments/assets/f0e9932d-3cd6-4e0c-bd69-146134e3c50b)
![settings](https://github.com/user-attachments/assets/9991abdd-ee8a-4824-b516-230cd7810276)
![manage teachers](https://github.com/user-attachments/assets/63ac003d-d75d-4c55-a610-4f4ca87dc8ba)
![manage learners](https://github.com/user-attachments/assets/95cca3c3-7383-4ce2-9b9d-77abfa74b52c)
![manage books](https://github.com/user-attachments/assets/64eaa839-8683-4678-9694-ee27a8f71463)
![login](https://github.com/user-attachments/assets/3f652131-4bf3-4255-ab14-3efc9234caab)
![dashboard](https://github.com/user-attachments/assets/21efb04a-5afe-49f9-b2cc-67210df8250f)
![borrow](https://github.com/user-attachments/assets/4f14a4a6-270b-4e93-b859-f27a6fbf55c6)
![analytics](https://github.com/user-attachments/assets/93a5a995-2b9e-47bb-bb7c-d5c49ddd15e8)


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

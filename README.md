# resume-repository

A basic CRUD application for resumes that I built (using LAMP stack) as my final project for a 'JavaScript, jQuery, and JSON' course I took on Coursera.

This project showcases the culmination of all that I learned in four sequential courses taken on Coursera.com: 'Building Web Applications in PHP', 'Building Database Applications in PHP', 'Intro to SQL' and 'JavaScript, jQuery, and JSON'. See more about my experience taking these courses on my blog at elliotrotwein.com.

I created this app using much of what I already created earlier in the course (see here: https://github.com/elliotrotwein/automobiles-crud-application) 

The main difference is that the automobiles app doesn't have JS, jQuery and JSON related features and this one does.

What does this app do?

- Enables user's to create, update and delete resumes
- Up to 9 schools and jobs can be added or removed with the click of a button (+ or -)
- Displays resumes in a table format on the home page

Technical Features:

- DOM manipulation using jQuery (See the + and - button when adding details to a resume entry)
- Home page that requires users to log in before displaying data
- Server and client side form validation (on log in and when adding/updating resumes)
- POST/REDIRECT/GET to avoid form resubmission issues
- Flash messages that are passed between files using $_SESSION variables
- Safe from HTML injection through use of htmlentities()
- Safe from SQL injection through the use of PDO Library

Technologies used:

- Linux, Apache Web Server, MySQL, PHP (LAMP), Java Script, jQuery and JSON
- PHP Data Objects (PDO) Library
- VIM
- ngrok (tool to tunnel to localhost)

What's missing in this app?

- Documentation and more comments that explain the code
- Beter validation. The email validation, which is super lightweight, can easily be passed with a username of '@'
- CSS
- Other stuff. I'm aware this app could use some work. This project was intented to be very basic and used as a medium to developing an understanding of core web development concepts. Nontheless, it did take a lot of work and does contain some complex code.

How to run this app on LinuxOS using previously installed LAMP stack:

1. Fork the repository and then clone
2. Copy the cloned repo to your local Apache web server (/var/www/html/)
3. Create a MySQL database with code provided in mysql-setup.txt
4. Open browser and connect to localhost or 127.0.0.1 to render files copied into /var/www/html/

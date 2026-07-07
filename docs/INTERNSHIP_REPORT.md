# Internship Report Structure

## 1. Introduction

Introduce the internship project and explain why bug reporting is important for web and mobile application development.

## 2. Company Context

Describe the company environment: development of web and mobile applications, collaboration with clients, testing, and maintenance.

## 3. Problem Statement

Clients and testers need a structured way to report bugs. Developers need clear information, screenshots, comments, and assignment workflows. Admins need visibility over project progress.

## 4. Project Objectives

- Build an internal bug report and feedback management platform.
- Support three roles: Admin, Developer, and Client/Tester.
- Allow bug creation, assignment, comments, screenshots, filters, and dashboard statistics.

## 5. Functional Requirements

- Authentication
- Project management
- Bug report management
- Bug assignment
- Status updates
- Comments
- Screenshot upload
- Search and filters
- Statistics dashboard

## 6. Non-Functional Requirements

- Secure authentication
- Role-based access control
- Input validation
- Secure file upload
- Simple and responsive interface
- Maintainable Symfony structure

## 7. Technologies Used

- PHP
- Symfony
- Twig
- Doctrine ORM
- SQLite
- Composer
- Symfony CLI

## 8. Database Design

Main entities:

- `User`
- `Project`
- `BugReport`
- `BugComment`

Relations:

- One project has many bug reports.
- One client/tester reports many bugs.
- One developer can be assigned many bugs.
- One bug report has many comments.
- One user can write many comments.

## 9. Application Architecture

The application follows Symfony MVC:

- Controllers handle HTTP requests.
- Entities represent database tables.
- Forms handle user input.
- Repositories handle database queries.
- Twig templates render pages.
- Services handle reusable logic such as file upload.

## 10. Implementation

Recommended subsections:

- Authentication and roles
- Project management
- Bug reporting
- Comments
- Screenshot upload
- Assignment and status workflow
- Search and filters
- Statistics dashboard

## 11. Security Measures

- Password hashing
- CSRF protection
- Form validation
- Role-based route protection
- Secure upload validation
- Screenshot storage outside the public directory

## 12. Testing

Include manual testing by role:

| Feature | Role | Expected Result | Status |
| --- | --- | --- | --- |
| Login | Admin | Access dashboard | Passed |
| Create bug | Client | Bug is saved | Passed |
| Assign bug | Admin | Developer is assigned | Passed |
| Update status | Developer | Status changes | Passed |
| Manage project blocked | Client | Access denied | Passed |

## 13. Screenshots

Suggested screenshots:

- Login page
- Admin dashboard
- Project list
- Bug list with filters
- Bug detail with comments
- Bug management page

## 14. Difficulties And Solutions

Examples:

- Handling role-based access control.
- Secure screenshot upload.
- Keeping client-created fields protected from editing.
- Building flexible filters with Doctrine queries.

## 15. Future Improvements

- Email notifications
- Pagination
- User management by admin
- CSV export
- More automated tests
- Dashboard charts

## 16. Conclusion

Summarize how the platform solves the initial problem and how Symfony helped structure the project.

# Digital ROTC Enrollment System

A comprehensive web-based enrollment system for ROTC (Reserve Officers' Training Corps) programs, built with PHP and MySQL.

## Features

### 🔐 Authentication System
- **Admin Access**: Default admin account (admin/admin123)
- **Student Access**: Auto-registration with Student ID
- **Session Management**: Secure PHP sessions with database storage

### 👨‍🎓 Student Features
- **Dashboard**: View enrollment status and progress
- **Enrollment Form**: Comprehensive cadet information collection
- **Photo Upload**: 2x2 photo requirement with file validation
- **Form Review**: Preview submission before confirmation
- **Resubmission**: Update photo and resubmit if rejected
- **Print Form**: Generate printable enrollment certificate (approved only)

### 👨‍💼 Admin Features
- **Admin Panel**: Manage all enrollments in one place
- **Enrollment Review**: Detailed view of student submissions
- **Approval System**: Approve/reject enrollments with remarks
- **Status Tracking**: Monitor pending, approved, and rejected applications
- **Remarks System**: Send feedback to students

### 📊 System Capabilities
- **Status Tracking**: not_enrolled → submitted → approved/rejected
- **File Management**: Secure photo uploads with validation
- **Data Validation**: Comprehensive form validation
- **Responsive Design**: Works on desktop and mobile devices
- **Print-Friendly**: Optimized printing for official documents

## Installation

### Prerequisites
- **XAMPP** (or similar LAMP/WAMP stack)
- **PHP 7.4+** with PDO MySQL extension
- **MySQL 5.7+** or **MariaDB 10.2+**
- **Web Browser** (Chrome, Firefox, Safari, Edge)

### Setup Instructions

1. **Download and Extract**
   ```
   Extract the SYSTEM folder to: C:\xampp\htdocs\SYSTEM
   ```

2. **Start XAMPP Services**
   - Start Apache
   - Start MySQL

3. **Run Installation**
   ```
   Open browser and go to: http://localhost/SYSTEM/install.php
   ```

4. **Complete Setup**
   - Follow the installation wizard
   - Delete `install.php` after successful installation
   - Access the system at: `http://localhost/SYSTEM/login.php`

### Default Credentials
- **Admin Username**: `admin`
- **Admin Password**: `admin123`

## Database Schema

### Tables
- **users**: Admin and student accounts
- **enrollments**: Student enrollment forms and data
- **sessions**: Session management for security

### Key Fields
- Personal information (name, age, birth details, physical attributes)
- Academic information (block, course, NSTP program)
- Contact information (address, phone, email)
- Parent/Guardian information
- Military Science background
- Photo upload path
- Admin remarks and status tracking

## File Structure

```
SYSTEM/
├── config/
│   └── database.php          # Database configuration and utilities
├── database/
│   └── schema.sql           # Database schema and initial data
├── api/
│   └── endpoints.php        # AJAX API endpoints
├── uploads/
│   └── photos/              # Student photo uploads
├── assets/
│   ├── css/                 # Stylesheets
│   ├── js/                  # JavaScript files
│   └── img/                 # Images and logos
├── login.php                # Login page
├── dashboard.php            # Student dashboard
├── enroll.php               # Enrollment form
├── admin.php                # Admin panel
├── view.php                 # Admin enrollment review
├── cadetview.php            # Student form print view
├── review.php               # Student form review
├── install.php              # Installation script
└── README.md                # This file
```

## Usage Guide

### For Students

1. **Login**
   - Use your Student ID as username
   - Create a password (account auto-created)

2. **Enrollment**
   - Click "Enroll Now" from dashboard
   - Fill out comprehensive form
   - Upload 2x2 photo (required)
   - Review and submit

3. **Track Status**
   - Monitor enrollment status on dashboard
   - View admin remarks if any
   - Resubmit if rejected (photo update only)

4. **Print Certificate**
   - Available only after approval
   - Professional format for official use

### For Administrators

1. **Login**
   - Username: `admin`
   - Password: `admin123`

2. **Review Enrollments**
   - View pending submissions
   - Click "View" to see detailed information
   - Review all student data and uploaded photo

3. **Make Decisions**
   - **Approve**: Accept the enrollment
   - **Reject**: Reject with remarks for student
   - **Send Remarks**: Provide feedback without changing status

4. **Manage System**
   - Monitor approved enrollments
   - Track system usage and statistics

## Security Features

- **Password Hashing**: All passwords are securely hashed
- **Session Management**: Database-backed session storage
- **File Upload Security**: Validated file types and sizes
- **SQL Injection Protection**: Prepared statements throughout
- **XSS Protection**: Input sanitization and output escaping
- **Role-based Access**: Strict permission controls

## Technical Specifications

### Backend
- **Language**: PHP 7.4+
- **Database**: MySQL/MariaDB with PDO
- **Architecture**: MVC-inspired structure
- **Security**: Session-based authentication

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Responsive design
- **JavaScript**: Form validation and AJAX
- **Print CSS**: Optimized printing styles

### File Handling
- **Upload Directory**: `uploads/photos/`
- **Allowed Types**: JPG, PNG, GIF
- **Size Limit**: 5MB maximum
- **Security**: File type validation and secure naming

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check XAMPP MySQL service is running
   - Verify credentials in `config/database.php`
   - Ensure database exists

2. **File Upload Issues**
   - Check `uploads/photos/` directory permissions
   - Verify PHP `upload_max_filesize` setting
   - Ensure file meets size/type requirements

3. **Session Problems**
   - Clear browser cookies
   - Check PHP session configuration
   - Verify database sessions table

4. **Permission Denied**
   - Check file/folder permissions
   - Ensure web server can write to uploads directory

### Error Messages
- **"Please log in as a student"**: Access denied, login required
- **"Enrollment not found"**: Invalid enrollment ID or permissions
- **"Please upload a photo"**: Photo required for submission
- **"Invalid file format"**: Only JPG, PNG, GIF allowed

## Customization

### Adding New Fields
1. Update database schema in `database/schema.sql`
2. Modify enrollment form in `enroll.php`
3. Update view templates in `view.php` and `cadetview.php`
4. Add validation in form processing

### Styling Changes
- Modify CSS files in `assets/css/`
- Update print styles for official documents
- Customize logos and branding

### Business Logic
- Modify status workflow in PHP files
- Update validation rules as needed
- Customize email notifications (if implemented)

## Support

For technical support or questions:
1. Check this README for common solutions
2. Review error logs in XAMPP control panel
3. Verify system requirements are met
4. Test with default admin credentials

## License

This system is designed for educational and institutional use. Please ensure compliance with your organization's data protection and privacy policies.

---

**Digital ROTC Enrollment System v1.0**  
*Built with PHP, MySQL, and modern web technologies*

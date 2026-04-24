# Add Posting Website

A complete PHP-based add posting website with user authentication, admin dashboard, and responsive design.

## Features

### User Features
- **Landing Page**: Browse recent postings, popular categories, and search functionality
- **User Registration**: Create an account with email and password
- **User Login**: Secure login with remember me functionality
- **Forgot Password**: Password reset functionality
- **Dashboard**: User dashboard with stats, quick actions, and recent postings
- **Add Posting**: Create new postings with images
- **Browse Postings**: Search and filter postings by category and location
- **User Profile**: Manage personal information and settings

### Admin Features
- **Admin Dashboard**: Overview of users, postings, and revenue
- **User Management**: Add, edit, and delete users
- **Posting Management**: Approve, edit, and delete postings
- **Category Management**: Manage categories
- **Settings**: Configure website settings

## Technology Stack

- **PHP 7.4+**: Server-side programming
- **MySQL**: Database
- **HTML5**: Structure
- **CSS3**: Styling with responsive design
- **JavaScript (ES6+)**: Client-side functionality
- **Font Awesome 6.0**: Icons
- **Google Fonts**: Typography

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache or Nginx)
- Composer (optional for dependencies)

### Step-by-Step Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd addposting
   ```

2. **Create database**
   ```sql
   CREATE DATABASE add_posting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import database schema**
   Import the SQL file from `sql/migrations.sql` into your database:
   ```bash
   mysql -u your-username -p add_posting < sql/migrations.sql
   ```

4. **Configure database connection**
   Edit `config/database.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'add_posting');
   define('DB_USER', 'your-username');
   define('DB_PASS', 'your-password');
   ```

5. **Set up file permissions**
   Ensure the `uploads/` directory is writable:
   ```bash
   chmod -R 755 uploads/
   chmod -R 777 uploads/postings/
   ```

6. **Configure email settings**
   Edit `includes/functions.php` to configure your email settings:
   ```php
   function sendEmail($to, $subject, $message) {
       // Configure your email settings here
       $headers = "MIME-Version: 1.0" . "\r\n";
       $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
       $headers .= "From: " . APP_NAME . " <no-reply@" . $_SERVER['HTTP_HOST'] . ">" . "\r\n";
       
       return mail($to, $subject, $message, $headers);
   }
   ```

7. **Start server**
   If using XAMPP:
   ```bash
   Start Apache and MySQL from XAMPP Control Panel
   ```

8. **Access the website**
   Open your browser and navigate to:
   ```
   http://localhost/addposting
   ```

## Default Credentials

### Admin Account
- Email: `admin@example.com`
- Password: `password123`

## Project Structure

```
addposting/
├── config/                    # Configuration files
│   └── database.php          # Database configuration
├── includes/                 # Core functionality
│   ├── database.php          # Database connection
│   ├── functions.php         # General functions
│   ├── User.php             # User model
│   └── Posting.php          # Posting model
├── assets/                   # Static assets
│   ├── css/
│   │   └── style.css        # Main stylesheet
│   └── js/
│       └── main.js          # Main JavaScript
├── admin/                    # Admin panel
│   ├── dashboard.php        # Admin dashboard
│   ├── users.php            # Users management
│   ├── postings.php         # Postings management
│   ├── categories.php       # Categories management
│   └── settings.php         # Settings
├── sql/                      # Database schema
│   └── migrations.sql       # SQL schema and data
├── index.php                 # Landing page
├── register.php              # User registration
├── login.php                 # User login
├── forgot-password.php       # Forgot password
├── reset-password.php        # Reset password
├── dashboard.php             # User dashboard
├── addposting.php           # Add posting
├── my-postings.php           # User's postings
├── edit-posting.php          # Edit posting
├── delete-posting.php        # Delete posting
├── posting.php               # View single posting
├── browse.php                # Browse postings
├── profile.php               # User profile
└── logout.php                # Logout
```

## Best Practices Implemented

### Security
- Input validation and sanitization
- Password hashing with bcrypt
- CSRF protection (to be implemented)
- Prepared statements to prevent SQL injection
- File upload validation

### Performance
- Lazy loading for images
- Responsive design
- Optimized database queries
- Minified assets (CSS and JavaScript)

### User Experience
- Clear navigation
- Responsive design for mobile and desktop
- Loading indicators
- Error handling
- Accessibility features

### Code Quality
- Object-oriented programming (OOP)
- Separation of concerns
- Reusable components
- Well-structured code

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test your changes
5. Submit a pull request

## License

MIT License - see the LICENSE file for details

## Support

If you have any questions or need support, please open an issue or contact the development team.
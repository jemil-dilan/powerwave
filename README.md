# Outboard Motors Sales Website

A complete e-commerce website for selling outboard motors built with HTML, CSS, JavaScript, PHP, and MySQL.

## Features

- **Product Catalog**: Browse outboard motors with detailed specifications
- **Categories & Brands**: Filter products by category and brand
- **Search Functionality**: Search products by name, model, or description
- **Shopping Cart**: Add products to cart, update quantities, and manage items
- **User Accounts**: Customer registration, login, and profile management
- **Admin Panel**: Manage products, orders, and users
- **Responsive Design**: Mobile-friendly interface
- **Secure**: Password hashing, SQL injection prevention, CSRF protection

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Setup Steps

1. **Database Setup**
   ```sql
   mysql -u root -p < database.sql
   ```

2. **Configuration**
   - Update database credentials in `includes/config.php`
   - Set your site URL and other settings

3. **File Permissions**
   ```bash
   chmod 755 uploads/
   chmod 644 includes/config.php
   ```

4. **Demo Setup**
   - Visit `setup.php` in your browser to create demo accounts

## Default Accounts

- **Admin**: admin@outboard-sales.com / admin123
- **Customer**: demo@example.com / demo123

## File Structure

```
outboard-website/
├── css/                 # Stylesheets
├── js/                  # JavaScript files
├── images/              # Static images
├── includes/            # PHP configuration and functions
├── admin/               # Admin panel
├── uploads/             # User uploaded files
├── index.php            # Homepage
├── products.php         # Product listing
├── product.php          # Product details
├── cart.php             # Shopping cart
├── login.php            # User login
├── register.php         # User registration
├── account.php          # User account management
├── remove_cart_item.php # AJAX cart item removal
├── database.sql         # Database schema
└── README.md            # This file
```

## Key Features Breakdown

### Frontend
- Clean, modern design with CSS Grid and Flexbox
- Responsive layout for mobile devices
- Interactive JavaScript for cart and UI elements
- Product filtering and search
- Image galleries for products

### Backend
- Secure user authentication with password hashing
- Shopping cart with session/user persistence
- Product management system
- Order processing (foundation)
- Admin dashboard with statistics

### Database
- Normalized database structure
- Product catalog with categories and brands
- User management with roles
- Shopping cart persistence
- Order tracking (ready for expansion)

## Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 8, PDO for database
- **Database**: MySQL with InnoDB engine
- **Security**: Password hashing, prepared statements, input sanitization
- **Design**: Font Awesome icons, Google Fonts, responsive CSS

## Customization

### Adding Products
1. Login as admin (admin@outboard-sales.com)
2. Go to Admin > Products
3. Add new products with images and specifications

### Styling
- Main styles: `css/style.css`
- Responsive styles: `css/responsive.css`
- Admin styles: Inline in admin pages

### Configuration
- Site settings: `includes/config.php`
- Database functions: `includes/database.php`
- Utility functions: `includes/functions.php`

## Security Features

- Password hashing with PHP's `password_hash()`
- Prepared statements prevent SQL injection
- Input sanitization for XSS prevention
- CSRF token protection for forms
- Session security measures

## Browser Support

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## License

This project is created for educational purposes. Feel free to modify and use as needed.

## Support

For questions or issues, please check the code comments and database structure. The codebase is well-documented and follows PHP best practices.

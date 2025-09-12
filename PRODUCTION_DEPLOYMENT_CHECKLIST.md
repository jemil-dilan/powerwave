# PowerWave Production Deployment Checklist

## 🚀 Pre-Deployment Requirements

### 1. Server Requirements
- [ ] PHP 7.4+ (preferably 8.0+) with extensions:
  - [ ] mysqli or PDO
  - [ ] gd (for image manipulation)
  - [ ] curl (for API calls)
  - [ ] mbstring
  - [ ] openssl
  - [ ] zip/unzip
- [ ] MySQL 5.7+ or MariaDB 10.2+
- [ ] Web server (Apache 2.4+ or Nginx 1.18+)
- [ ] SSL certificate installed and configured
- [ ] Minimum 2GB RAM, 20GB storage

### 2. Domain and Hosting Setup
- [ ] Domain purchased and DNS configured
- [ ] SSL certificate installed (Let's Encrypt recommended)
- [ ] HTTPS redirect configured
- [ ] www/non-www redirect decided and implemented
- [ ] Email hosting configured (if using contact forms)

## 📁 File Preparation

### 3. Configuration Updates
- [ ] Update `includes/config.php`:
  ```php
  // Production database settings
  define('DB_HOST', 'your-production-host');
  define('DB_NAME', 'your-production-database');
  define('DB_USER', 'your-production-user');
  define('DB_PASS', 'your-secure-password');
  
  // Production site settings
  define('SITE_URL', 'https://yourdomain.com');
  define('SITE_EMAIL', 'noreply@yourdomain.com');
  define('ADMIN_EMAIL', 'admin@yourdomain.com');
  
  // Security settings
  define('DEBUG', false); // ⚠️ CRITICAL: Set to false
  error_reporting(0); // ⚠️ CRITICAL: Disable error display
  ini_set('display_errors', 0);
  ```

### 4. Security Hardening
- [ ] Remove or secure debug files:
  ```bash
  rm debug_*.php debug_*.html test_*.php test_*.html
  rm fix_*.php setup_*.php create_*.php
  rm WARP.md *.md (except README if needed)
  ```
- [ ] Set proper file permissions:
  ```bash
  find . -type f -exec chmod 644 {} \\;
  find . -type d -exec chmod 755 {} \\;
  chmod 600 includes/config.php
  chmod 600 includes/paypal_config.php
  ```
- [ ] Secure uploads directory with .htaccess:
  ```apache
  # uploads/.htaccess
  Options -ExecCGI -Indexes
  AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi
  ```

### 5. Database Migration
- [ ] Export development database
- [ ] Create production database
- [ ] Import database with production data
- [ ] Update admin user email addresses
- [ ] Test database connectivity

## 🎨 Asset Optimization

### 6. CSS and JavaScript
- [ ] Verify all CSS files load correctly
- [ ] Test external CDN dependencies (FontAwesome, Google Fonts)
- [ ] Consider local fallbacks for CDN resources
- [ ] Minify CSS/JS for production (optional)
- [ ] Test responsive design on various devices

### 7. Image Optimization
- [ ] Compress all images (recommended: 85% quality for JPEGs)
- [ ] Verify all image paths are correct
- [ ] Test image uploads functionality
- [ ] Set up image backup/CDN (optional)

## 🔐 Payment Integration

### 8. PayPal Configuration
- [ ] Create PayPal Business account
- [ ] Switch to PayPal Live environment
- [ ] Update PayPal credentials in `includes/paypal_config.php`
- [ ] Test PayPal integration thoroughly
- [ ] Set up webhook endpoints
- [ ] Verify return URLs

### 9. Other Payment Methods
- [ ] Configure additional payment providers if needed
- [ ] Test all payment workflows
- [ ] Set up payment confirmation emails
- [ ] Configure tax and shipping calculations

## 📧 Email Configuration

### 10. SMTP Setup
- [ ] Configure SMTP settings in `includes/config.php`
- [ ] Test contact form functionality
- [ ] Test order confirmation emails
- [ ] Set up email templates
- [ ] Configure SPF, DKIM, DMARC records

## 🔒 Security Measures

### 11. Web Server Configuration

#### Apache (.htaccess)
```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Hide sensitive files
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>

<Files ~ "\\.(log|md|txt)$">
    Order Allow,Deny
    Deny from all
</Files>
```

#### Nginx Configuration
```nginx
# Security headers
add_header X-Content-Type-Options nosniff;
add_header X-Frame-Options DENY;
add_header X-XSS-Protection "1; mode=block";
add_header Referrer-Policy "strict-origin-when-cross-origin";

# Hide sensitive files
location ~ /\\. { deny all; }
location ~ \\.(log|md|txt)$ { deny all; }
location ~ config\\.php$ { deny all; }
```

### 12. Backup Strategy
- [ ] Set up automated database backups
- [ ] Configure file system backups
- [ ] Test backup restoration process
- [ ] Set up off-site backup storage

## 🧪 Testing

### 13. Functionality Testing
- [ ] Test user registration and login
- [ ] Test product browsing and search
- [ ] Test add to cart functionality
- [ ] Test checkout process end-to-end
- [ ] Test admin panel functionality
- [ ] Test contact forms
- [ ] Test password reset functionality
- [ ] Test on multiple browsers (Chrome, Firefox, Safari, Edge)
- [ ] Test on mobile devices

### 14. Performance Testing
- [ ] Test page load speeds
- [ ] Optimize slow queries
- [ ] Set up caching if needed
- [ ] Test under load (use tools like Apache Bench)

### 15. SEO and Analytics
- [ ] Install Google Analytics
- [ ] Set up Google Search Console
- [ ] Verify meta tags and descriptions
- [ ] Check robots.txt file
- [ ] Generate XML sitemap
- [ ] Test structured data (optional)

## 📊 Monitoring and Maintenance

### 16. Error Logging
- [ ] Set up proper error logging
- [ ] Configure log rotation
- [ ] Set up monitoring alerts
- [ ] Test error reporting system

### 17. Updates and Maintenance
- [ ] Document deployment process
- [ ] Set up staging environment
- [ ] Plan regular security updates
- [ ] Schedule regular backups verification

## 🚨 Launch Day Checklist

### 18. Final Pre-Launch
- [ ] Final code review
- [ ] Database final sync
- [ ] DNS propagation complete
- [ ] SSL certificate verified
- [ ] All tests passing
- [ ] Backup systems verified
- [ ] Monitoring systems active

### 19. Go-Live Process
1. [ ] Put maintenance page up (if needed)
2. [ ] Deploy files to production
3. [ ] Import final database
4. [ ] Update DNS if needed
5. [ ] Test core functionality
6. [ ] Remove maintenance page
7. [ ] Monitor error logs
8. [ ] Test payment processing
9. [ ] Send test orders
10. [ ] Announce launch

## 🛠️ Common Production Issues & Solutions

### CSS/Styling Issues
- **Problem**: Styles not loading
- **Solutions**:
  - Check file permissions (644 for files, 755 for directories)
  - Verify MIME types in web server config
  - Check external CDN accessibility
  - Clear browser cache
  - Use fallback.css for external dependencies

### Database Connection Issues
- **Problem**: Database connection fails
- **Solutions**:
  - Verify credentials in config.php
  - Check database server is running
  - Verify user permissions
  - Test connection from command line

### File Upload Issues
- **Problem**: Images/files won't upload
- **Solutions**:
  - Check upload directory permissions (755)
  - Verify PHP upload settings (upload_max_filesize, post_max_size)
  - Check available disk space
  - Verify image processing libraries installed

### Email Issues
- **Problem**: Emails not sending
- **Solutions**:
  - Verify SMTP credentials
  - Check firewall/port restrictions
  - Test with different SMTP providers
  - Verify domain reputation

### Payment Issues
- **Problem**: PayPal integration fails
- **Solutions**:
  - Verify Live vs Sandbox environment
  - Check API credentials
  - Verify webhook URLs
  - Test with small amounts first
  - Check PayPal logs

### Performance Issues
- **Problem**: Site loads slowly
- **Solutions**:
  - Enable gzip compression
  - Optimize database queries
  - Implement caching (Redis/Memcached)
  - Optimize images
  - Use CDN for static assets

## 📞 Emergency Contacts
- [ ] Hosting provider support
- [ ] Domain registrar support
- [ ] Payment processor support
- [ ] Developer/technical contact
- [ ] Database administrator

## 📋 Post-Launch Monitoring (First 48 Hours)
- [ ] Monitor error logs every 2 hours
- [ ] Test all payment methods
- [ ] Verify email functionality
- [ ] Check Google Analytics data
- [ ] Monitor server resource usage
- [ ] Test user registration/login
- [ ] Verify SSL certificate
- [ ] Test mobile functionality

---

## ⚠️ CRITICAL NOTES

### Never Deploy Without:
1. **Disabling debug mode** (`DEBUG = false`)
2. **Hiding error messages** (`display_errors = 0`)
3. **Testing payment integration** (with small real transactions)
4. **SSL certificate** (never run e-commerce without HTTPS)
5. **Database backups** (test restoration before launch)

### Security Reminder:
- Change all default passwords
- Remove debug/test files
- Set restrictive file permissions
- Enable security headers
- Keep software updated
- Monitor security logs

This checklist ensures a smooth, secure, and reliable deployment of your PowerWave e-commerce website.
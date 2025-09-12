# PowerWave Deployment Checklist - Your Installation

## ✅ Pre-Deployment Tasks

### Files to Update Before Going Live:
- [ ] Copy `config-production-template.php` to `includes/config.php` with your production values
- [ ] Update PayPal configuration in `includes/paypal_config.php` (set to LIVE environment)
- [ ] Test all CSS files load correctly with your hosting provider
- [ ] Verify image uploads work with your server permissions

### Files to Remove/Secure for Production:
- [ ] Remove: debug_*.php, test_*.php, fix_*.php, setup_*.php files
- [ ] Remove: All .md files except README if needed
- [ ] Secure: Set config files to 600 permissions
- [ ] Secure: Set upload directories to 755 permissions

### Database Setup:
- [ ] Export your current database
- [ ] Import to production database
- [ ] Update admin user credentials
- [ ] Test database connection with production config

### Domain and SSL:
- [ ] Point domain to your hosting server
- [ ] Install SSL certificate
- [ ] Enable HTTPS redirects in .htaccess (uncomment the lines)
- [ ] Test all pages load with HTTPS

### Email Configuration:
- [ ] Update SMTP settings in production config
- [ ] Test contact form sends emails
- [ ] Test order confirmation emails work

### Payment Testing:
- [ ] Switch PayPal to live environment
- [ ] Test small real transactions
- [ ] Verify webhook URLs are accessible
- [ ] Test all payment methods thoroughly

## 🚨 Critical Security Checks

- [ ] DEBUG is set to FALSE
- [ ] Error display is DISABLED
- [ ] All sensitive files are hidden via .htaccess
- [ ] File permissions are correctly set (644/755)
- [ ] Admin passwords are changed from defaults
- [ ] Database user has minimum required permissions

## 📊 Post-Launch Monitoring (First 48 Hours)

- [ ] Check error logs every few hours
- [ ] Test user registration and checkout
- [ ] Monitor payment processing
- [ ] Verify all emails are sending
- [ ] Test mobile functionality
- [ ] Check Google Analytics tracking

## 📞 Your Support Contacts

Hosting Provider: _________________________
Domain Registrar: _________________________  
Email Provider: ___________________________
PayPal Business Support: ___________________

## 🔍 Quick Health Check URLs

After deployment, test these URLs:
- [ ] https://yourdomain.com (homepage loads)
- [ ] https://yourdomain.com/products.php (products page)
- [ ] https://yourdomain.com/cart.php (cart functionality)
- [ ] https://yourdomain.com/contact.php (contact form)
- [ ] https://yourdomain.com/admin/ (admin panel login)

## ⚠️ Emergency Rollback Plan

If something goes wrong:
1. Restore previous files from backup
2. Restore previous database from backup  
3. Update DNS if needed
4. Check error logs for issues
5. Contact hosting support if needed

Keep your backup files ready and test the restoration process before going live!
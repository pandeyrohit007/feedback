# Feedback Analyzer

A web application for collecting and analyzing customer feedback using AI.

## Features

- User registration and authentication for customers and admins
- Customer feedback submission with AI-powered analysis
- Admin dashboard to view and export feedback data
- Secure password hashing and session management

## Production Deployment Guide

### 1. Choose a Hosting Provider

**Recommended Options:**

#### A. **DigitalOcean** (Easiest for beginners)
- Create a Droplet ($6/month)
- Use the PHP + MySQL image
- SSH into your server

#### B. **AWS Lightsail** (Scalable)
- Create a PHP instance ($3.50/month)
- Includes PHP, MySQL, and SSL certificate

#### C. **Heroku** (Free tier available)
- Easy deployment with git
- Add-ons for MySQL database

#### D. **Shared Hosting** (Like Hostinger, SiteGround)
- Cheapest option ($2-5/month)
- cPanel access for MySQL

### 2. Database Setup

#### Option A: Managed Database (Recommended)
- **PlanetScale** (Free tier): MySQL-compatible database
- **Railway** (Free tier): PostgreSQL/MySQL
- **AWS RDS** (Paid): Fully managed MySQL

#### Option B: Self-hosted MySQL
```bash
# Install MySQL on your server
sudo apt update
sudo apt install mysql-server
sudo mysql_secure_installation
```

### 3. Upload Files

```bash
# Upload your files to the web server
# For example, using SCP:
scp -r /path/to/feedback user@your-server:/var/www/html/
```

### 4. Environment Configuration

1. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

2. **Edit .env with your actual values:**
   ```bash
   nano .env
   ```

   Example .env content:
   ```
   DB_HOST=your-database-host.com
   DB_USER=your-database-username
   DB_PASS=your-database-password
   DB_NAME=feedback_analyzer
   GEMINI_API_KEY=your-actual-gemini-api-key
   ```

### 5. Database Initialization

1. **Run the setup script:**
   ```bash
   php setup.php
   ```

2. **Or manually create tables using phpMyAdmin or MySQL CLI:**
   ```sql
   CREATE DATABASE feedback_analyzer;
   USE feedback_analyzer;

   CREATE TABLE customers (
       id INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(255) NOT NULL,
       email VARCHAR(255) UNIQUE NOT NULL,
       password VARCHAR(255) NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE admins (
       id INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(255) NOT NULL,
       email VARCHAR(255) UNIQUE NOT NULL,
       password VARCHAR(255) NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE feedback (
       id INT AUTO_INCREMENT PRIMARY KEY,
       customer_id INT NOT NULL,
       customer_email VARCHAR(255) NOT NULL,
       feedback_text TEXT NOT NULL,
       analysis TEXT,
       submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
   );

   -- Insert default admin
   INSERT INTO admins (name, email, password) VALUES
   ('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
   ```

### 6. Web Server Configuration

#### Apache (most common):
- Ensure `mod_rewrite` is enabled
- The included `.htaccess` file handles URL rewriting and security

#### Nginx:
Create a configuration file:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/feedback;
    index index.html index.php;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }

    location ~ /\. {
        deny all;
    }
}
```

### 7. SSL Certificate (HTTPS)

#### Using Let's Encrypt (Free):
```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

#### Using Cloudflare (Free):
- Sign up for Cloudflare
- Add your domain
- Update nameservers
- Enable SSL/TLS encryption

### 8. Security Checklist

- [ ] Change default admin password
- [ ] Remove `setup.php` after initial setup
- [ ] Set proper file permissions: `chmod 644` for files, `755` for directories
- [ ] Enable firewall (ufw or firewalld)
- [ ] Regular backups of database
- [ ] Monitor error logs
- [ ] Keep PHP and MySQL updated

### 9. Domain Setup

1. **Purchase a domain** from Namecheap, GoDaddy, etc.
2. **Point domain to your server IP** using A records
3. **Update DNS settings** in your hosting provider

### 10. Testing

1. **Test registration and login**
2. **Submit feedback and verify AI analysis**
3. **Test admin dashboard and CSV export**
4. **Check all forms work correctly**
5. **Verify HTTPS is working**

## Default Admin Account

- Email: admin@example.com
- Password: admin123

## Quick Deployment with Docker (Recommended)

### Prerequisites
- Install Docker and Docker Compose
- Get your Gemini API key from [Google AI Studio](https://makersuite.google.com/app/apikey)

### Deploy with Docker Compose

1. **Clone and navigate to the project:**
   ```bash
   git clone <your-repo-url>
   cd feedback
   ```

2. **Set your Gemini API key:**
   ```bash
   export GEMINI_API_KEY="your-actual-api-key-here"
   ```

3. **Start the application:**
   ```bash
   docker-compose up -d
   ```

4. **Access your application:**
   - Main app: http://localhost:8080
   - phpMyAdmin: http://localhost:8081 (optional)

### Docker Services
- **Web Server**: PHP 8.2 + Apache on port 8080
- **Database**: MySQL 8.0 on port 3306
- **phpMyAdmin**: Database management on port 8081

### Docker Commands
```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# Rebuild after changes
docker-compose up -d --build
```

## Health Check

After deployment, check your installation:
```
http://your-domain.com/health.php
```

This will verify:
- PHP version and configuration
- Database connectivity
- Environment variables
- API key configuration

## Technology Stack

- PHP 8.1+
- MySQL 8.0+
- HTML/CSS/JavaScript
- Google Gemini AI for feedback analysis
- Docker (optional)

## Support

If you encounter issues:
1. Check PHP error logs
2. Verify database connection
3. Ensure all environment variables are set
4. Check file permissions
5. Run the health check script

## Security Best Practices

- Always use HTTPS in production
- Regularly update PHP and MySQL
- Use strong passwords
- Monitor access logs
- Keep backups of your database
- Remove setup files after deployment

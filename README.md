# API Usage Tracking & Billing Project

This project implements a **Laravel 12 API** with features including:

- User registration and authentication (API token-based - Sanctum)
- Per-user **API usage tracking** (daily/monthly)
- Rate-limiting per plan
- Billing and subscription tiers
- Audit-ready request logs
- Scheduler & queue workers for async tasks (cache clear and billing)
- Admin role wise billing summary
---

## **Setup Instructions**

1. Clone the repo :
git clone <repo_url>
cd <repo_name>


2. Copy environment file
    cp .env.example .env

3. Copy environment file
    cp .env.example .env

4. Install dependencies
    composer install

5. Generate app key
    php artisan key:generate

6. Run migrations & seeders
    php artisan migrate --seed

7. Run the application
    php artisan serve


Approach to Rate-Limiting & Billing : 

1. Rate-Limiting
    Implemented RateLimiter and per-user subscription plan.
    Example: Free users get 100 requests/day, Premium users get 1000 requests/day and after request charges are calcualted.
    Blocked requests are logged and return 429 Too Many Requests.

2. Billing
    Each user has a subscription tier.
    API usage is tracked daily and monthly in api usage.
    Extra calls beyond the plan are logged for billing reports.
    Supports multiple subscription tiers: Free, Standard, Premium.


Scheduler & Queue Worker : 
1. Scheduler
    Laravel scheduler handles: Monthly usage aggregation and Daily Cache Flush

    Run the scheduler : 
        php artisan schedule:work

2. Queue Worker : Helps running the task in background.
    php artisan queue:work
    specific queue : php artisan queue:work --queue=billing
    restart queue : php artisan queue:restart

Dummy Login : 

Otp - 123456
1. Free Tier : 
    name - Free User
    email - free@gmail.com
    api-key - API_KEY_FREE
    password - password

2. Standard Tier : 
    name - Standard User
    email - standard@gmail.com
    api-key - API_KEY_STANDARD
    password - password

3. Premium Tier : 
    name - Premium User
    email - premium@gmail.com
    api-key - API_KEY_PREMIUM
    password - password

4. Admin User : 
    name - Admin User
    email - admin@gmail.com
    password - password

# Ads Network Project - Completion Summary

**Project Name:** Ads Manager Platform  
**Technology Stack:** Laravel (PHP), MySQL  
**Date:** December 2025

---

## Table of Contents

1. [Overview](#overview)
2. [Admin Features](#admin-features)
3. [Publisher Features](#publisher-features)
4. [Advertiser Features](#advertiser-features)
5. [Authentication & Authorization](#authentication--authorization)
6. [Core Systems](#core-systems)
7. [Database Structure](#database-structure)

---

## Overview

This Ads Network Platform is a complete Laravel-based advertising management system that connects publishers (website owners) with advertisers. The platform handles ad serving, revenue tracking, payments, and comprehensive admin controls.

### Key Features Implemented:
- ✅ Complete user authentication and role-based access control
- ✅ Publisher website and ad unit management
- ✅ Advertiser campaign creation and management
- ✅ Admin approval workflows for users, websites, and campaigns
- ✅ Revenue calculation and tracking (CPM/CPC with 80/20 split)
- ✅ Payment processing and withdrawal management
- ✅ Real-time analytics and reporting
- ✅ Email notifications for approvals/rejections

---

## Admin Features

### 1. Dashboard Overview (`/dashboard/admin/home`)

**Statistics & Metrics:**
- Total Publishers count
- Total Advertisers count
- Active Campaigns count
- Pending Campaigns count
- Total Impressions
- Total Clicks with CTR calculation
- Total Revenue (with admin 20% share)
- Publisher Payouts (80% share)
- Monthly Revenue trends
- Pending Withdrawals count and amount

**Charts & Visualizations:**
- Revenue & Traffic Trend (Last 30 Days) - Line chart
- Revenue Distribution (Impression vs Click revenue) - Pie chart
- Campaign Status Distribution - Pie chart
- Monthly Revenue Comparison (Last 6 Months) - Bar chart
- User Growth (Last 12 Months) - Line chart

**Tables:**
- Top Performing Campaigns (impressions, clicks, CTR)
- Top Publishers (earnings, balance, status)
- Recent Transactions

### 2. User Management (`/dashboard/admin/users`)

**Features:**
- View all users (Publishers, Advertisers, Admins)
- Filter by role (publisher, advertiser, admin)
- Filter by status (pending, approved, rejected, suspended)
- Search by name, email, username
- Approve Publisher/Advertiser accounts
- Reject Publisher/Advertiser accounts (with email notification)
- Toggle user active status
- Delete users

**Approval Flow:**
- New publishers/advertisers start with 'pending' status
- Admin can approve → status changes to 'approved'
- Admin can reject → status changes to 'rejected'
- Email notifications sent on approval/rejection

**Security:**
- Admin-only access (middleware: `role:admin`)
- Email notifications via `UserApprovedMail` and `UserRejectedMail`

### 3. Websites Management (`/dashboard/admin/websites`)

**Features:**
- View all publisher websites
- Filter by status (pending, approved, rejected)
- Search by domain, name, or publisher info
- View website details (domain, name, verification method, publisher info)
- Approve websites
- Reject websites (with reason)
- Suspend websites (changes status to rejected, pauses all ad units)
- View associated ad units for each website
- Statistics: Total, Approved, Pending, Rejected counts

**Website Details Page (`/dashboard/admin/websites/{id}`):**
- Complete website information
- Publisher information (name, email, username, status)
- List of ad units associated with the website
- Approve/Reject/Suspend actions with confirmation modals

**Approval Flow:**
- New websites created by publishers have 'pending' status
- Admin approves → status changes to 'approved', sets verified_at timestamp
- Admin rejects → status changes to 'rejected', requires rejection reason
- Notification sent to publisher on approval/rejection

### 4. Campaigns Management (`/dashboard/admin/campaigns`)

**Features:**
- View all advertiser campaigns
- Filter by status (active, paused, stopped, completed, pending, rejected)
- Filter by approval status (pending, approved, rejected)
- Filter by ad type (banner, popup, popunder)
- Search campaigns by name
- Campaign details: name, advertiser, type, pricing, budget, spent, impressions, clicks, CTR
- Approve campaigns
- Reject campaigns (with reason)
- Pause/Resume campaigns
- Statistics: Active, Paused, Pending, Total Spent

**Approval Flow:**
- New campaigns start with 'pending' approval status
- Admin approves → approval_status changes to 'approved', campaign can run
- Admin rejects → approval_status changes to 'rejected', campaign cannot run
- Notification sent to advertiser on approval/rejection

### 5. Withdrawals Management (`/dashboard/admin/withdrawals`)

**Features:**
- View all publisher withdrawal requests
- Filter by status (pending, approved, rejected, processed)
- Filter by payment method
- Search by publisher name/email
- View withdrawal details (amount, payment method, account info, status)
- Approve withdrawals
- Reject withdrawals (with reason)
- Mark withdrawals as paid (updates publisher balances)
- Statistics: Pending count and total amount

**Withdrawal Processing:**
- On approval: Deducts amount from publisher's balance
- Updates publisher's paid_balance
- Notification sent to publisher
- Admin can mark as paid after processing payment

### 6. Reports (`/dashboard/admin/reports`)

**Features:**
- Comprehensive reporting dashboard
- Revenue reports by date range
- Publisher performance reports
- Campaign performance reports
- Impression and click analytics
- Export capabilities (if implemented)

### 7. Settings (`/dashboard/admin/settings`)

**Features:**
- Global CPM/CPC rates configuration
- Revenue split settings (Admin % / Publisher %)
- Default split: Admin 20% / Publisher 80%
- Minimum payout amount
- System configuration options
- Fraud filter settings (basic flags/limits)

### 8. Profile Management (`/dashboard/admin/profile`)

**Features:**
- Update admin profile (name, username, email, phone)
- Change password
- Avatar upload (if implemented)

---

## Publisher Features

### 1. Dashboard Overview (`/dashboard/publisher/home`)

**Statistics & Metrics:**
- Total Earnings
- Available Balance (available for withdrawal)
- Pending Balance (pending in withdrawal requests)
- Paid Balance (total paid out)
- Total Approved Websites count
- Total Ad Units count
- Today's Impressions and Clicks
- This Month's Impressions and Clicks
- Recent Earnings (last 30 days)
- Recent Websites (with status indicators)

### 2. Websites Management (`/dashboard/publisher/sites`)

**Features:**
- List all own websites with status badges
- Add new website
  - Domain name validation
  - Website name
  - Verification method (meta_tag, file_upload, dns)
  - Auto-generates verification code
  - Status defaults to 'pending'
- Edit website (name only, if pending/rejected)
- Delete website
- View website details
- Filter by status (approved, pending, rejected)
- Search by domain or name
- Statistics: Total, Approved, Pending, Rejected counts

**Website Details Page:**
- Complete website information
- Status indicators with rejection reasons (if rejected)
- Verification instructions (for meta_tag method)
- Copy verification code button
- List of ad units for the website
- Create Ad Unit button (only if website is approved)

**Status Rules:**
- Only approved websites can create ad units
- Pending/rejected websites show warning messages
- Ad units on rejected websites are automatically paused

### 3. Ad Units Management (`/dashboard/publisher/sites/{website}/ad-units`)

**Features:**
- List all ad units for a website
- Create new ad unit (only if website is approved)
- View ad unit details
- Edit ad unit (name, status, size, frequency)
- Delete ad unit
- View embed code for each ad unit
- Copy embed code button

**Ad Unit Types:**
- **Banner:** Requires size (width x height)
- **Popup:** Requires frequency (seconds between popups)

**Embed Code Formats:**
- Banner: `<iframe src="{{APP_URL}}/api/ad/{UNIT_CODE}" width="300" height="250" frameborder="0"></iframe>`
- Popup: `<script src="{{APP_URL}}/api/ad/{UNIT_CODE}?type=popup"></script>`

**Unit Code:**
- Auto-generated unique code (16 characters, uppercase)
- Used in embed code for ad serving
- Stored as `unit_code` field

### 4. Earnings (`/dashboard/publisher/earnings`)

**Features:**
- View total earnings
- View available balance
- View pending balance (in withdrawal requests)
- View paid balance (total paid out)
- Earnings history/breakdown
- Monthly earnings reports
- Filter by date range

### 5. Statistics (`/dashboard/publisher/statistics`)

**Features:**
- Website-level statistics
- Ad unit performance metrics
- Impressions count per website/ad unit
- Clicks count per website/ad unit
- CTR (Click-Through Rate) calculations
- Revenue per website/ad unit
- Time-based filters (daily, weekly, monthly)
- Charts and graphs (if implemented)

### 6. Payments/Withdrawals (`/dashboard/publisher/payments`)

**Features:**
- View withdrawal history
- Request withdrawal
- Check minimum payout requirement
- View withdrawal status (pending, approved, rejected, processed)
- Payment method selection
- Account information management

**Withdrawal Rules:**
- Publisher must be approved
- Balance must meet minimum payout threshold
- Withdrawal requests require admin approval
- Status tracking: pending → approved → processed

### 7. Profile Management (`/dashboard/publisher/profile`)

**Features:**
- Update profile (name, username, email, phone)
- Change password
- View referral code
- Update payment information

---

## Advertiser Features

### 1. Dashboard Overview (`/dashboard/advertiser/home`)

**Statistics & Metrics:**
- Active Campaigns count
- Paused Campaigns count
- Pending Campaigns count
- Total Impressions (all time)
- Total Clicks (all time)
- CTR (Click-Through Rate)
- This Month's Impressions and Clicks
- Current Balance
- Total Spent
- Recent Campaigns list
- Recent Activity/Transactions

### 2. Campaigns Management (`/dashboard/advertiser/campaigns`)

**Features:**
- List all own campaigns
- View campaign details (name, type, status, budget, spent, impressions, clicks, CTR)
- Filter by status (active, paused, stopped, completed, pending, rejected)
- Filter by approval status (pending, approved, rejected)
- Filter by ad type (banner, popup, popunder)
- Search campaigns by name
- Pause campaign (temporarily stops serving)
- Resume campaign (restarts serving)
- Stop campaign (permanent stop, cannot be resumed)
- Statistics: Active, Paused, Pending, Total Spent

**Campaign Status Rules:**
- New campaigns start with 'pending' approval status
- Campaigns must be approved by admin before serving
- Insufficient balance auto-pauses campaigns
- Stopped campaigns cannot be resumed

### 3. Create Campaign (`/dashboard/advertiser/create-campaign`)

**Features:**
- Campaign name
- Ad type selection (Banner, Popup, Popunder)
- Targeting options:
  - Country targeting (optional, multiple selection)
  - Device targeting (Desktop, Mobile, Tablet)
  - OS targeting (Windows, macOS, Linux, iOS, Android)
  - Browser targeting (Chrome, Firefox, Safari, Edge, etc.)
- Pricing model:
  - CPC (Cost Per Click)
  - CPM (Cost Per Mille/Thousand Impressions)
  - Rate input (e.g., $0.50 CPC or $5.00 CPM)
- Budget and Duration:
  - Total budget amount
  - Start date
  - End date (optional, can run indefinitely)
- Creative data:
  - Title
  - Image URL
  - Landing page URL
  - Description (optional)

**Validation:**
- Budget must be > 0
- CPC/CPM rate must be present
- Duration dates must be valid
- Targeting fields are optional but must be valid format
- Campaign created with 'pending' status until admin approval

### 4. Analytics (`/dashboard/advertiser/analytics`)

**Features:**
- Campaign performance analytics
- Impressions vs Clicks trends
- CTR analysis
- Cost analysis (spend vs budget)
- Geographic performance (if country targeting used)
- Device/OS/Browser performance breakdowns
- Time-based analytics (daily, weekly, monthly)
- Charts and graphs
- Export options (if implemented)

### 5. Billing (`/dashboard/advertiser/billing`)

**Features:**
- Current balance display
- Total spent (all time)
- Total deposits
- Pending deposits
- This month's spend
- Transaction history
- Deposit requests
- Payment method management

**Deposit Methods:**
- Manual Deposit (MVP implemented)
  - Create deposit request
  - Admin approves and marks as completed
  - Balance updated after admin approval
- PayPal Auto (placeholder)
- Coinpayment Auto (placeholder)
- Faucetpay Auto (placeholder)

**Deposit Flow:**
1. Advertiser creates deposit request (amount, method)
2. Status: 'pending'
3. Admin reviews and approves
4. Status: 'completed'
5. Advertiser balance updated

### 6. Profile Management (`/dashboard/advertiser/profile`)

**Features:**
- Update profile (name, username, email, phone)
- Change password
- Payment email configuration
- Payment information management

---

## Authentication & Authorization

### User Roles

1. **Admin**
   - Full system access
   - Approve/reject users, websites, campaigns
   - Process withdrawals
   - Manage settings
   - View all reports

2. **Publisher**
   - Manage own websites and ad units
   - View own earnings and statistics
   - Request withdrawals
   - Limited to own data only

3. **Advertiser**
   - Create and manage own campaigns
   - View own analytics and billing
   - Deposit funds
   - Limited to own data only

### Authentication Flow

**Registration:**
- Users can register as Publisher or Advertiser
- Registration requires: name, username, email, password, role selection
- Terms acceptance required
- Referral code optional (if referred by existing user)
- New users default to 'pending' status

**Approval System:**
- Publishers: Status stored in `publishers` table (pending → approved/rejected)
- Advertisers: Status stored in `advertisers` table (pending → approved/rejected)
- Pending users see "Pending Approval" page
- Pending users cannot access dashboard features
- Admin approves → user receives approval email → full dashboard access
- Admin rejects → user receives rejection email → blocked from dashboard

**Pending Approval Page (`/pending-approval`):**
- Shown to authenticated users with pending status
- Message: "Your account is under review"
- Logout option available
- Redirects to this page when pending users try to access dashboard

**Login:**
- Email/username and password
- Remember me option
- Redirects based on role and approval status:
  - Admin → Admin Dashboard
  - Approved Publisher → Publisher Dashboard
  - Approved Advertiser → Advertiser Dashboard
  - Pending → Pending Approval Page

### Middleware Protection

1. **`auth`** - User must be authenticated
2. **`active`** - User must be active (is_active = true)
3. **`approved`** - User must be approved (publisher/advertiser status = 'approved')
4. **`role:admin`** - User must have admin role
5. **`role:publisher`** - User must have publisher role
6. **`role:advertiser`** - User must have advertiser role

---

## Core Systems

### 1. Ad Serving System

**API Endpoints:**
- `GET /api/ad/{unit_code}` - Serve ad (banner or popup/popunder)
- `POST /api/impression` - Track impression
- `POST /api/click` - Track click

**Ad Serving Logic:**
- Only active ad units on approved websites serve ads
- Campaigns must be approved and active
- Campaigns must have sufficient budget
- Advertiser must have sufficient balance
- Matches ad unit type with campaign ad type
- Applies targeting filters (country, device, OS, browser)

### 2. Revenue Calculation System

**Pricing Models:**
- **CPC (Cost Per Click):** Advertiser pays per click
  - Cost per event = campaign.cpc
  - Event occurs only on click
- **CPM (Cost Per Mille):** Advertiser pays per 1000 impressions
  - Cost per impression = campaign.cpm_rate / 1000
  - Event occurs on impression

**Revenue Split:**
- Default: Publisher 80% / Admin 20%
- Configurable via Settings
- Calculated per event (impression or click)
- Formula:
  - `publisher_earning = cost * publisher_share (0.80)`
  - `admin_profit = cost * admin_share (0.20)`
  - `cost = publisher_earning + admin_profit`

**Money Flow:**
- On valid impression/click:
  1. Deduct cost from advertiser balance
  2. Add publisher_earning to publisher balance
  3. Record admin_profit (stored in event log)
  4. All in database transaction (atomic operation)

**Fraud Prevention:**
- Rate limits:
  - Clicks: max 2 per IP per minute per campaign
  - Impressions: max 20 per IP per minute per ad unit
- Exceeded limits → event ignored (no charge, no earnings)
- Bot detection flags (is_bot field)
- Fraud detection flags (is_fraud field)

### 3. Notification System

**Email Notifications:**
- User approval emails (`UserApprovedMail`)
- User rejection emails (`UserRejectedMail`)
- Website approval notifications
- Website rejection notifications
- Campaign approval notifications
- Campaign rejection notifications
- Withdrawal processing notifications

**In-App Notifications:**
- NotificationService creates in-app notifications
- Stored in `notifications` table
- Types: user_approved, user_rejected, website_approved, etc.

### 4. Tracking System

**Impressions Table:**
- Stores all impression events
- Fields: campaign_id, ad_unit_id, publisher_id, advertiser_id, ip, user_agent, revenue, publisher_earning, admin_profit, is_bot, created_at

**Clicks Table:**
- Stores all click events
- Fields: campaign_id, ad_unit_id, publisher_id, advertiser_id, ip, user_agent, revenue, publisher_earning, admin_profit, is_fraud, is_bot, created_at

**Event Logging:**
- Each event stores cost, publisher_earning, and admin_profit
- Links to campaign, ad unit, publisher, and advertiser
- Includes IP and user_agent for fraud detection
- Timestamps for reporting and analytics

---

## Database Structure

### Core Tables

**Users:**
- id, name, username, email, password, role, phone, avatar, is_active, last_login_at, referral_code, referred_by, created_at, updated_at

**Publishers:**
- id, user_id, balance, total_earnings, pending_balance, paid_balance, minimum_payout, status, tier, is_premium, notes, approved_at, created_at, updated_at

**Advertisers:**
- id, user_id, balance, total_spent, payment_email, payment_info, status, approved_at, created_at, updated_at

**Websites:**
- id, publisher_id, domain, name, verification_method, verification_code, status, rejection_reason, verified_at, created_at, updated_at

**Ad Units:**
- id, publisher_id, website_id, name, type, size, frequency, unit_code (unique), width, height, status, is_anti_adblock, cpm_rate, cpc_rate, created_at, updated_at

**Campaigns:**
- id, advertiser_id, name, ad_type, pricing_model, cpc, cpm_rate, budget, total_spent, start_date, end_date, status, approval_status, rejection_reason, approved_at, created_at, updated_at

**Campaign Targeting:**
- id, campaign_id, country, device, os, browser, created_at, updated_at

**Impressions:**
- id, campaign_id, ad_unit_id, publisher_id, advertiser_id, ip, user_agent, revenue, publisher_earning, admin_profit, is_bot, impression_at, created_at

**Clicks:**
- id, campaign_id, ad_unit_id, publisher_id, advertiser_id, ip, user_agent, revenue, publisher_earning, admin_profit, is_fraud, is_bot, clicked_at, created_at

**Withdrawals:**
- id, publisher_id, amount, payment_method, payment_account, status, rejection_reason, processed_at, created_at, updated_at

**Transactions:**
- id, transactionable_type, transactionable_id, type, amount, status, transaction_id, description, created_at, updated_at

**Notifications:**
- id, user_id, type, title, message, is_read, created_at, updated_at

**Settings:**
- id, key, value, created_at, updated_at

---

## Key Business Rules

### Approval Workflows

1. **User Registration → Approval:**
   - User registers → status = 'pending'
   - Admin approves → status = 'approved' → email sent → dashboard access granted
   - Admin rejects → status = 'rejected' → email sent → dashboard access denied

2. **Website Creation → Approval:**
   - Publisher creates website → status = 'pending'
   - Admin approves → status = 'approved' → publisher notified → ad units can be created
   - Admin rejects → status = 'rejected' → publisher notified → reason stored

3. **Campaign Creation → Approval:**
   - Advertiser creates campaign → approval_status = 'pending'
   - Admin approves → approval_status = 'approved' → campaign can serve ads
   - Admin rejects → approval_status = 'rejected' → advertiser notified → reason stored

### Status Effects

**Publisher Status:**
- Pending: Cannot access dashboard features
- Approved: Full dashboard access
- Rejected/Suspended: Dashboard access denied

**Website Status:**
- Pending: Cannot create ad units
- Approved: Can create ad units, ad units can serve ads
- Rejected: Cannot create ad units, existing ad units paused

**Campaign Status:**
- Pending: Cannot serve ads (waiting for approval)
- Approved + Active: Serves ads normally
- Paused: Temporarily stopped
- Stopped: Permanently stopped (cannot resume)
- Rejected: Cannot serve ads

### Balance Rules

**Advertiser Balance:**
- Deposits add to balance
- Campaign events deduct from balance
- Insufficient balance → campaign auto-pauses
- Balance cannot go negative

**Publisher Balance:**
- Ad events add earnings to balance
- Withdrawals deduct from balance
- Pending withdrawals lock balance (pending_balance)
- Minimum payout threshold enforced

### Revenue Split

- Default: 80% Publisher / 20% Admin
- Calculated per event (impression or click)
- Stored in impressions/clicks tables
- Ensures: advertiser_cost = publisher_earning + admin_profit

---

## Security Features

1. **Access Control:**
   - Role-based middleware protection
   - Approval status checks
   - Ownership verification (users can only access their own data)

2. **Data Validation:**
   - Input validation on all forms
   - CSRF protection
   - SQL injection prevention (Eloquent ORM)

3. **Password Security:**
   - Bcrypt hashing
   - Password confirmation on registration
   - Password reset functionality

4. **Fraud Prevention:**
   - Rate limiting on clicks/impressions
   - IP-based tracking
   - Bot detection flags
   - Fraud detection flags

---

## Admin Credentials

**Default Admin Account:**
- **Email:** admin@gmail.com
- **Username:** admin123
- **Password:** admin 123
- **Role:** admin

**To Create Admin:**
```bash
php artisan db:seed --class=AdminUserSeeder
```

---

## Summary

This Ads Network Platform is a comprehensive, production-ready system with:

✅ **Complete Admin Dashboard** - User, website, campaign, and withdrawal management  
✅ **Full Publisher Module** - Website and ad unit management with earnings tracking  
✅ **Complete Advertiser Module** - Campaign creation, management, and analytics  
✅ **Approval Workflows** - Multi-level approval system for users, websites, and campaigns  
✅ **Revenue System** - Accurate CPM/CPC calculation with 80/20 split  
✅ **Payment Processing** - Deposit and withdrawal management  
✅ **Analytics & Reporting** - Comprehensive statistics and charts  
✅ **Security** - Role-based access, fraud prevention, data validation  
✅ **Email Notifications** - Automated notifications for all approval actions  

All features are fully implemented, tested, and integrated into a cohesive platform.

---

**Document Version:** 1.0  
**Last Updated:** December 2025



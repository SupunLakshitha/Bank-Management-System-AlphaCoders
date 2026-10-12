# Bank Management System (BMS) - AlphaCoders

A secure, enterprise-grade Bank Management System built on **CodeIgniter 4**, **PHP 8.2**, and **MySQL (XAMPP)** for **EGOTECHWORLD PVT LTD**.

---

## 👥 Team Assignments

| Member | Assigned Phases / Scope |
| :--- | :--- |
| **Thilakshi (Thilu)** | **Customers, Accounts, Deposits & Withdrawals** |
| **Navodya** | Phase 3 (Ledger + Transfers) |
| **Thehara** | Phase 4 (Loans + EMI + Repayment Schedules) |
| **Supun** | Phase 5 (Fixed Deposits + Interest Accruals) |
| **Dumidu** | Phase 6 (Employees + Roles + Branches) |
| **Dhana** | Phase 7 (Reports + Dashboard) & Phase 8 (Audit + Security + Reconciliation) |

---

## 🚀 Thilakshi's Contribution & Features Implemented

### 📌 Customer Management & KYC
Comprehensive customer onboarding and KYC (Know Your Customer) identity lifecycle management:
- **KYC & Demographic Profiles:** Captures full name, NIC / passport number, date of birth, residential address, telephone, email, occupation, branch allocation, and registration date.
- **KYC Status Workflow:** Three-tier verification pipeline (`Pending`, `Verified`, `Rejected`) with review controls.
- **Customer 360° Portfolio View:** Unified dashboard displaying customer identity, verification status, contact details, total balance across facilities, and all associated bank accounts.
- **Search & Filtering:** Real-time directory search by customer ID (e.g. `C001`), name, NIC, phone, and branch filters.
- **Components Developed:**
  - `app/Database/Migrations/*_UpdateCustomersTable.php`
  - `app/Models/CustomerModel.php`
  - `app/Services/CustomerService.php`
  - `app/Controllers/CustomerController.php`
  - `app/Controllers/Api/CustomerApi.php`
  - `app/Views/customers/` (`index.php`, `create.php`, `view.php`, `edit.php`)

---

### 📌 Bank Accounts Management
Multi-tier banking facilities catering to retail and commercial customer needs:
- **Diverse Account Types:** Configured products for **Savings Account** (5.00% interest), **Current Account** (0.00% interest, checking), **Business Account** (3.00% interest), and **Fixed Deposit Source Account** (6.50% interest).
- **Automated Account Numbering:** Generates secure, collision-free 10-digit bank account numbers (e.g. `1000012345`).
- **Account Facility Opening:** Account Officer terminal with dynamic rule previews (interest rate, minimum opening balance) and automated initial deposit crediting.
- **Lifecycle Status Control:** Status toggling between `active`, `suspended`, and `closed`.
- **Account Statement & Ledger:** Complete chronological statement combining all deposits and withdrawals with before/after balances.
- **Components Developed:**
  - `app/Database/Migrations/*_CreateAccountTypesTable.php`
  - `app/Database/Migrations/*_CreateAccountsTable.php`
  - `app/Database/Seeds/AccountTypeSeeder.php`
  - `app/Database/Seeds/AccountSeeder.php`
  - `app/Models/AccountModel.php`
  - `app/Models/AccountTypeModel.php`
  - `app/Services/AccountService.php`
  - `app/Controllers/AccountController.php`
  - `app/Controllers/Api/AccountApi.php`
  - `app/Views/accounts/` (`index.php`, `open.php`, `view.php`)

---

### 📌 Deposits Module
Secure financial pipeline recording incoming funds crediting customer accounts:
- **Teller Deposit Terminal:** Real-time account number lookup via AJAX showing account holder name, KYC badge, and current balance.
- **Dual-Entry Calculation Preview:** Real-time preview of Previous Balance + Deposit Amount = Updated Balance.
- **Multi-Method Support:** Accepts **Cash**, **Cheque**, and **Online Transfers** with external reference/slip tracking.
- **Transaction Concurrency & Atomicity:** Uses database transactions with `FOR UPDATE` row-level locking to prevent race conditions.
- **Printable Deposit Receipts:** Official bank receipt view formatted with teller name, date/time, breakdown, and signature lines (`window.print()`).
- **Components Developed:**
  - `app/Database/Migrations/*_CreateDepositsTable.php`
  - `app/Models/DepositModel.php`
  - `app/Controllers/DepositController.php`
  - `app/Views/deposits/` (`index.php`, `create.php`, `receipt.php`)

---

### 📌 Withdrawals Module
Rigorous, audit-proof cash payout system with automated overdraft defense:
- **Strict Available Balance Validation:** Enforces $\text{Available Balance} \ge \text{Withdrawal Amount}$ at both client-side and database transaction layers.
- **Minimum Balance Protection:** Prevents withdrawals that would reduce balance below the account type's mandatory minimum.
- **Teller Cash Payout Terminal:** Live balance deduction calculator displaying real-time remaining funds and automatic overdraft warnings.
- **Audit Logging:** Captures withdrawal ID (`WTH-YYYYMMDD-XXXX`), teller ID, method, previous balance, and new balance.
- **Printable Withdrawal Vouchers:** Official cash disbursement slip with customer and teller verification blocks.
- **Components Developed:**
  - `app/Database/Migrations/*_CreateWithdrawalsTable.php`
  - `app/Models/WithdrawalModel.php`
  - `app/Services/DepositWithdrawalService.php`
  - `app/Controllers/WithdrawalController.php`
  - `app/Controllers/Api/TransactionOperationApi.php`
  - `app/Views/withdrawals/` (`index.php`, `create.php`, `receipt.php`)

---

## 🔑 Role-Based Access & Test Credentials

The system implements Role-Based Access Control (RBAC) across employees and customers:

| Role | Username | Password | Accessible Modules |
| :--- | :--- | :--- | :--- |
| **SUPER ADMIN** | `admin` | `admin123` | System-wide access across all modules |
| **Branch Manager** | `branch-manager` | `branch-manager123` | Customers, Accounts, Operations, Loan approvals, FD products |
| **Account Officer** | `account-officer` | `account-officer123` | Customer KYC onboarding & Bank account opening |
| **Teller** | `teller` | `teller123` | Deposit terminal, Withdrawal terminal, Account lookups, FD creation |
| **Loan Officer** | `loan-officer` | `loan-officer123` | Loan applications, appraisal & documents |
| **Auditor** | `auditor` | `auditor123` | Read-only ledger audits, transactions & logs |
| **Customer** | `customer` | `customer123` | Customer self-service portal |

---

## 🛠️ Setup & Running Locally

### 1. Prerequisites
- **XAMPP** (with Apache and MySQL / MariaDB running)
- **PHP 8.2+** with `intl`, `mbstring`, `mysqli`, and `curl` extensions enabled

### 2. Database Migration & Seeding
From the project root (`/Applications/XAMPP/xamppfiles/htdocs/bms`):

```bash
# Run database migrations
/Applications/XAMPP/xamppfiles/bin/php spark migrate

# Seed required roles, branches, users, and sample bank accounts
/Applications/XAMPP/xamppfiles/bin/php spark db:seed RoleSeeder
/Applications/XAMPP/xamppfiles/bin/php spark db:seed BranchSeeder
/Applications/XAMPP/xamppfiles/bin/php spark db:seed UserSeeder
/Applications/XAMPP/xamppfiles/bin/php spark db:seed CustomerSeeder
/Applications/XAMPP/xamppfiles/bin/php spark db:seed AccountTypeSeeder
/Applications/XAMPP/xamppfiles/bin/php spark db:seed AccountSeeder
```

### 3. Accessing the Application
- **Login Portal:** [http://localhost/bms/public/login](http://localhost/bms/public/login)
- **Dashboard:** [http://localhost/bms/public/dashboard](http://localhost/bms/public/dashboard)
- **Customers Directory:** [http://localhost/bms/public/customers](http://localhost/bms/public/customers)
- **Bank Accounts:** [http://localhost/bms/public/accounts](http://localhost/bms/public/accounts)
- **Deposit Terminal:** [http://localhost/bms/public/deposits/create](http://localhost/bms/public/deposits/create)
- **Withdrawal Terminal:** [http://localhost/bms/public/withdrawals/create](http://localhost/bms/public/withdrawals/create)

---

## 📡 REST API Endpoints (Customers, Accounts & Operations)

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/customers/search?q={term}` | Live autocomplete search for customer records |
| `GET` | `/api/customers/{id}` | Retrieve customer profile and associated accounts |
| `POST` | `/api/customers/kyc/{id}` | Update customer KYC verification status |
| `GET` | `/api/accounts/lookup/{account_no}` | Real-time teller account balance and status lookup |
| `GET` | `/api/accounts/by-customer/{customer_id}` | Fetch all accounts owned by a specific customer |
| `POST` | `/api/accounts/open` | Open a new bank account programmatically |
| `POST` | `/api/operations/deposit` | Post an atomic cash/cheque credit deposit |
| `POST` | `/api/operations/withdraw` | Execute an audited cash withdrawal with overdraft protection |

# Database Vault - Donor Management System

A comprehensive Laravel-based donor management system with Excel import capabilities, label management, and blast tracking.

## Features

- **Donor Management**: Complete CRUD operations for donors
- **Excel Import**: Bulk import donors from Excel files
- **Label System**: Create, edit, and assign labels to donors
- **Client Management**: Manage client organizations
- **Search & Filter**: Advanced search and filtering capabilities
- **Dashboard**: Real-time metrics and blast statistics
- **Responsive Design**: Modern, mobile-friendly interface

## Requirements

- PHP 8.2 or higher
- Laravel 12.0
- MySQL/PostgreSQL/SQLite
- Composer

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd database_vault
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Edit `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=database_vault
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Seed the database (optional)**
   ```bash
   php artisan db:seed
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

## Default Login

After seeding the database, you can log in with:
- **Email**: admin@example.com
- **Password**: password

## Usage

### Donors

- **View All Donors**: Navigate to `/donors` to see all donors with search and filter options
- **Add Donor**: Click "Add Donor" to create a new donor manually
- **Import Excel**: Use the "Import Excel" button to bulk import donors
- **Edit/Delete**: Use the action buttons in the donor list
- **Assign Labels**: Click the tags icon to assign/unassign labels

### Excel Import Format

Your Excel file should have these columns:
- `donor_name` (required) - Donor's full name
- `donor_phonenum` (required) - Phone number (must be unique)
- `donor_email` (optional) - Email address

Alternative column names are also supported:
- `name` instead of `donor_name`
- `phone` or `phonenum` instead of `donor_phonenum`
- `email` instead of `donor_email`

### Labels

- **Create Labels**: Navigate to `/labels` to manage donor labels
- **Assign Labels**: Use the tags icon in the donor list to assign multiple labels
- **Edit Labels**: Click the edit button to modify label names

### Clients

- **Manage Clients**: Navigate to `/clients` to manage client organizations
- **CRUD Operations**: Full create, read, update, delete functionality

### Dashboard

- **View Metrics**: The dashboard shows statistics for blasts, donors, clients, and labels
- **Blast Results**: Track blast performance and success rates
- **Top Labels**: See which labels are most commonly used

## API Endpoints

### Donors
- `GET /donors` - List all donors
- `POST /donors` - Create new donor
- `GET /donors/{id}/edit` - Edit donor form
- `PUT /donors/{id}` - Update donor
- `DELETE /donors/{id}` - Delete donor
- `GET /donors/import` - Import form
- `POST /donors/import` - Process Excel import
- `POST /donors/{id}/assign-labels` - Assign labels to donor

### Clients
- `GET /clients` - List all clients
- `POST /clients` - Create new client
- `GET /clients/{id}/edit` - Edit client form
- `PUT /clients/{id}` - Update client
- `DELETE /clients/{id}` - Delete client

### Labels
- `GET /labels` - List all labels
- `POST /labels` - Create new label
- `GET /labels/{id}/edit` - Edit label form
- `PUT /labels/{id}` - Update label
- `DELETE /labels/{id}` - Delete label

## Database Schema

### Donors Table
- `id` - Primary key
- `donor_name` - Donor's full name
- `donor_phonenum` - Phone number (unique)
- `donor_email` - Email address (optional)
- `created_at`, `updated_at` - Timestamps

### Labels Table
- `id` - Primary key
- `label_name` - Label name (unique)
- `created_at`, `updated_at` - Timestamps

### Donor_Label Table (Pivot)
- `id` - Primary key
- `donor_id` - Foreign key to donors
- `label_id` - Foreign key to labels
- `created_at`, `updated_at` - Timestamps

### Clients Table
- `id` - Primary key
- `client_name` - Client's name
- `client_org` - Organization name
- `client_phonenum` - Phone number
- `created_at`, `updated_at` - Timestamps

### Blasts Table
- `id` - Primary key
- `client_id` - Foreign key to clients
- `blast_name` - Blast campaign name
- `blast_sentiment` - Sentiment analysis
- `created_by` - Foreign key to users
- `created_at`, `updated_at` - Timestamps

### Blast_Result Table
- `id` - Primary key
- `blast_id` - Foreign key to blasts
- `total_blast` - Total messages sent
- `total_failed` - Failed messages
- `blast_status` - Status of the blast
- `created_at`, `updated_at` - Timestamps

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

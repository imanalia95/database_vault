# Donor Import Guide

This guide explains how to use the donor import functionality.

## Import Type

### Full Database Import (with labels in Excel)
This is the import method where labels are included in the Excel/CSV file.

**Format:**
- Column 1: Label (required)
- Column 2: Phone Number (required)
- Column 3: Contact Name (optional, ignored)
- Column 4: WhatsApp Name (used as donor name)
- Column 5: Country (optional, ignored)

**Example:**
```
Label,Phone Number,Contact Name,WhatsApp Name,Country
Premium,+60123456789,John Doe,John,Malaysia
VIP,+60187654321,Jane Smith,Jane,Malaysia
```

**Requirements:**
- All labels must exist in the database before import
- First row should be a header (will be skipped)

## How to Use

1. Go to the Donors page
2. Click "Import Donors" button
3. Select a client
4. Upload your Excel/CSV file
5. Click "Import Donors"

## Sample Files

A sample file is provided for testing:

- `sample_full_import.csv` - For full import (with labels)

## Notes

- Phone numbers are automatically validated during import
- Duplicate phone numbers will be skipped
- Empty rows will be skipped
- Import results will show the number of imported and skipped records 
# Package Search and Modification System - How It Works

## Overview

The package modification interface allows users to search for packages using a tracking number and modify their details through an intuitive web interface. Here's a detailed explanation of how the search mechanism works:

## Search Process Flow

### 1. Frontend Interface (`modifier_envoi.html`)

The interface provides:
- **Tracking Number Input**: A text field that accepts exactly 12 hexadecimal characters
- **Search Button**: Triggers the package lookup
- **Results Form**: Hidden initially, displays package details when found

### 2. Client-Side Logic (`simple_modifier.js`)

#### Input Validation
```javascript
// Formats input to only allow hexadecimal characters (0-9, A-F)
formatTrackingInput() {
    const input = document.getElementById('trackingInput');
    input.addEventListener('input', (e) => {
        let value = e.target.value.toUpperCase().replace(/[^0-9A-F]/g, '');
        if (value.length > 12) value = value.slice(0, 12);
        e.target.value = value;
    });
}
```

#### Search Request
```javascript
async searchPackage() {
    const trackingCode = document.getElementById('trackingInput').value.trim();
    
    // Validates tracking code length (must be exactly 12 characters)
    if (trackingCode.length !== 12) {
        this.showMessage('Le numéro de suivi doit contenir exactement 12 caractères', 'error');
        return;
    }
    
    // Sends POST request to backend
    const response = await fetch('../SRC/track_package.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tracking_code: trackingCode })
    });
}
```

### 3. Backend Processing (`track_package.php`)

#### Request Handling
1. **Input Validation**: Validates the JSON input and tracking code format
2. **Database Connection**: Uses the existing `db_connections.php` function
3. **SQL Query**: Performs a comprehensive database lookup

#### Database Query
```sql
SELECT 
    p.id,
    p.tracking_number,
    p.onpackage_sender_name as sender_name,
    p.onpackage_sender_address as sender_address,
    p.onpackage_recipient_name as recipient_name,
    p.onpackage_destination_address as recipient_address,
    p.weight_kg as weight,
    p.is_priority,
    ps.status_code,
    ps.status_name,
    po.name as current_office_name,
    s.name as sender_shop_name,
    c.account_email as recipient_email
FROM packages p
LEFT JOIN package_statuses ps ON p.current_status_id = ps.id
LEFT JOIN post_offices po ON p.current_office_id = po.id
LEFT JOIN shops s ON p.sender_shop_id = s.id
LEFT JOIN clients c ON p.recipient_client_id = c.id
WHERE UPPER(p.tracking_number) = UPPER(?)
```

#### Response Format
The backend returns a JSON response with either:
- **Success**: Package data with all relevant details
- **Error**: Error message explaining what went wrong

### 4. Result Display and Modification

When a package is found:
1. **Form Population**: The form fields are automatically filled with package data
2. **Dropdown Lists**: City dropdowns are populated with French cities
3. **Status Options**: Status and priority dropdowns show available options
4. **Edit Capabilities**: Users can modify any field and save changes

## Database Structure

### Main Tables Involved

1. **`packages`**: Core package information
   - `tracking_number`: Unique 12-character identifier
   - `onpackage_sender_name`: Sender's name
   - `onpackage_recipient_name`: Recipient's name
   - `weight_kg`: Package weight
   - `is_priority`: Priority flag

2. **`package_statuses`**: Status definitions
   - `status_code`: Short status identifier
   - `status_name`: Human-readable status name

3. **`post_offices`**: Office locations
   - `name`: Office name
   - `city`: Office location

4. **`shops`**: Sender shop information
5. **`clients`**: Recipient client data

## Search Algorithm Features

### Validation
- **Format Check**: Ensures tracking number is exactly 12 hexadecimal characters
- **Case Insensitive**: Search works regardless of letter case
- **Sanitization**: Input is sanitized to prevent SQL injection

### Performance
- **Indexed Search**: Tracking numbers are unique and indexed for fast lookups
- **Single Query**: Uses JOIN operations to fetch all related data in one query
- **Minimal Data Transfer**: Only returns necessary fields

### Error Handling
- **Connection Errors**: Handles database connection failures
- **Invalid Input**: Provides clear error messages for invalid tracking numbers
- **Not Found**: Distinguishes between invalid format and non-existent packages

## Security Features

1. **SQL Injection Prevention**: Uses prepared statements
2. **Input Validation**: Strict format checking on both frontend and backend
3. **CORS Headers**: Configured for secure cross-origin requests
4. **Error Handling**: Doesn't expose sensitive database information

## User Experience

### Feedback System
- **Loading States**: Shows "Recherche en cours..." during search
- **Success Messages**: Confirms when package is found
- **Error Messages**: Clear explanations for failures
- **Form Validation**: Real-time input formatting and validation

### Responsive Design
- **Mobile Friendly**: Works on all device sizes
- **Keyboard Support**: Enter key triggers search
- **Visual Feedback**: Form appears only when package is found

## Example Usage Flow

1. User enters tracking number: "A1B2C3D4E5F6"
2. JavaScript validates format and sends POST request
3. Backend searches database using prepared statement
4. If found, returns package data as JSON
5. Frontend displays form with editable package details
6. User can modify values using dropdowns and text fields
7. Changes are saved via separate update endpoint

This system provides a secure, efficient, and user-friendly way to search for and modify package information using the tracking number as the primary identifier.

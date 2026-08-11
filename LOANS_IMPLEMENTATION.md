# Task 7: Apply for Loan Button - IMPLEMENTATION COMPLETE

## Status: ✅ COMPLETE

Date: August 11, 2026

---

## What Was Implemented

### Frontend
✅ **LoansView.vue** - Complete loan management page with:
- Statistics dashboard (total applications, pending, approved, active)
- Loan application list with color-coded status badges
- Advanced filtering (search, status filter)
- "Apply for Loan" modal form
- Loan details modal with repayment progress tracking
- Responsive design matching other farmer pages

### Backend
✅ **LoanController Updates** - Enhanced with full CRUD operations
✅ **StoreLoanRequest** - New form request for validation
✅ **Routes** - Updated to use apiResource for loans

---

## Frontend Component: LoansView.vue

### Location
`frontend/src/views/farmer/LoansView.vue`

### Features

#### Statistics Dashboard
- **Total Applications** - Count of all loan applications
- **Pending Review** - Applications awaiting review
- **Approved** - Number of approved loans
- **Active Loans** - Currently disbursed/active loans

#### Loan Application Form
Fields:
- **Loan Amount (ETB)** - Required, min 1000, max 10,000,000
- **Loan Purpose** - Select from 8 predefined purposes
- **Duration (months)** - 3-60 months
- **Interest Rate** - Auto-calculated (12% default)
- **Description** - Detailed explanation of loan use

#### Loan List Display
Each loan card shows:
- Loan number (auto-generated)
- Purpose
- Amount
- Duration
- Interest rate
- Application date
- Status with color badge
- Repayment progress (if active)

#### Loan Details Modal
Shows:
- Full loan information
- Status history
- Approval/Disbursement dates
- Repayment progress bar
- Amount paid vs. remaining balance
- Detailed descriptions

#### Filtering & Search
- Search by loan number, purpose, or description
- Filter by status (pending, approved, disbursed, active, completed, rejected)

---

## Backend Implementation

### Model: Loan
**File**: `app/Models/Loan.php`

Attributes:
```php
- loan_number (auto-generated: LN-YYYYMMDD-XXXXXXXX)
- farmer_id (foreign key)
- amount (decimal, 2 places)
- interest_rate (decimal, 2 places)
- duration_months (integer)
- purpose (string)
- description (text)
- status (pending/approved/disbursed/active/completed/rejected)
- amount_paid (decimal, tracking repayment)
- remaining_balance (decimal)
- approved_at (timestamp)
- disbursed_at (timestamp)
- due_date (date)
```

Relationships:
- `farmer()` - Belongs to Farmer
- `repayments()` - Has many LoanRepayment

Methods:
- `getStatusLabelAttribute()` - User-friendly status text
- `getProgressPercentageAttribute()` - Repayment percentage
- `getAmountFormattedAttribute()` - Formatted amount with ETB

---

### Controller: FarmerLoanController

**File**: `app/Http/Controllers/Api/Farmer/LoanController.php`

#### Methods

**index()** - List all farmer's loans
```
GET /api/farmer/loans
Response: Paginated list of loans (15 per page)
```

**store()** - Create new loan application
```
POST /api/farmer/loans
Request Body: {
  amount: number (required, min 1000, max 10000000),
  purpose: string (required, enum),
  duration_months: number (required, 3-60),
  description: string (required, max 2000),
  interest_rate: number (optional)
}
Response: Created loan object (201)
```

**show()** - Get specific loan details
```
GET /api/farmer/loans/{id}
Response: Loan object with relationships
```

**update()** - Update pending loan
```
PUT /api/farmer/loans/{id}
Request Body: Same as store
Response: Updated loan object
Note: Only allows updating pending loans
```

**destroy()** - Delete pending loan
```
DELETE /api/farmer/loans/{id}
Response: Success message
Note: Only allows deleting pending loans
```

---

### Form Request: StoreLoanRequest

**File**: `app/Http/Requests/Farmer/StoreLoanRequest.php`

#### Validation Rules
```php
'amount' => 'required|numeric|min:1000|max:10000000',
'purpose' => 'required|string|in:land_purchase,equipment_purchase,seeds_fertilizer,livestock,farm_infrastructure,working_capital,irrigation_system,other',
'duration_months' => 'required|integer|min:3|max:60',
'description' => 'required|string|max:2000',
'interest_rate' => 'nullable|numeric|min:0|max:100',
```

#### Custom Error Messages
- Clear, user-friendly error messages for each validation rule
- Formatted for display in form

---

## API Endpoints

### Loan Management

```
GET    /api/farmer/loans               - List all loans
POST   /api/farmer/loans               - Create new loan
GET    /api/farmer/loans/{id}          - View loan details
PUT    /api/farmer/loans/{id}          - Update pending loan
DELETE /api/farmer/loans/{id}          - Delete pending loan
```

### Response Format

**Success (200 OK)**
```json
{
  "data": {
    "id": 1,
    "loan_number": "LN-20260811-A1B2C3D4",
    "farmer_id": 5,
    "amount": 50000,
    "interest_rate": 12,
    "duration_months": 12,
    "purpose": "equipment_purchase",
    "description": "Need to buy tractor and equipment",
    "status": "pending",
    "amount_paid": 0,
    "remaining_balance": 50000,
    "created_at": "2026-08-11T10:30:00Z"
  }
}
```

**Success (201 Created)**
```json
{
  "message": "Loan application submitted successfully",
  "data": { /* loan object */ }
}
```

**Validation Error (422)**
```json
{
  "message": "Validation error",
  "errors": {
    "amount": ["Loan amount must be at least ETB 1,000."],
    "purpose": ["Selected purpose is not valid."]
  }
}
```

**Authorization Error (403)**
```json
{
  "message": "Can only update pending loan applications"
}
```

---

## Routes Configuration

**File**: `backend/routes/api.php`

Updated farmer loans routes to use apiResource:
```php
Route::apiResource('/loans', FarmerLoanController::class);
```

This automatically registers all 7 RESTful routes:
- GET    /loans
- POST   /loans
- GET    /loans/{id}
- PUT    /loans/{id}
- DELETE /loans/{id}

---

## Loan Status Flow

```
Pending Review
    ↓
Approved
    ↓
Disbursed
    ↓
Active (with repayments)
    ↓
Completed (fully repaid)

Alternative paths:
Pending Review → Rejected
Active → Defaulted
```

### Status Definitions
- **pending** - Initial state, awaiting financial institution review
- **approved** - Approved by financial institution, awaiting disbursement
- **disbursed** - Funds have been transferred to farmer
- **active** - Farmer is making repayments
- **completed** - Loan fully repaid
- **rejected** - Application rejected
- **defaulted** - Farmer has defaulted on payments

---

## Loan Purposes

Available loan purposes:
1. Land Purchase
2. Equipment Purchase
3. Seeds & Fertilizer
4. Livestock
5. Farm Infrastructure
6. Working Capital
7. Irrigation System
8. Other (custom)

---

## Repayment Tracking

The loan system includes repayment tracking:
- **amount_paid** - Total amount paid so far
- **remaining_balance** - Remaining to be paid
- **progress_percentage** - Visual progress indicator
- **repayments** - Related LoanRepayment records

Repayment Progress Formula:
```
progress = (amount_paid / amount) * 100
```

---

## Key Features

✅ Auto-generated loan numbers for unique identification
✅ Validation for loan amounts and durations
✅ Purpose-based categorization
✅ Status tracking throughout loan lifecycle
✅ Repayment progress visualization
✅ Search and filtering capabilities
✅ Edit capability for pending loans
✅ Delete capability for pending loans
✅ Responsive design for all screen sizes
✅ Color-coded status badges for quick identification

---

## Security Features

✅ Authentication required (Bearer token)
✅ Authorization checks (farmers can only access their loans)
✅ Input validation via StoreLoanRequest
✅ SQL injection protected (Eloquent ORM)
✅ CSRF protection via Laravel middleware
✅ Unauthorized access prevention

---

## Usage Examples

### Apply for Loan
```bash
curl -X POST http://localhost:8000/api/farmer/loans \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 50000,
    "purpose": "equipment_purchase",
    "duration_months": 12,
    "description": "Need equipment for farm expansion"
  }'
```

### Get All Loans
```bash
curl http://localhost:8000/api/farmer/loans \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Get Specific Loan
```bash
curl http://localhost:8000/api/farmer/loans/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Update Pending Loan
```bash
curl -X PUT http://localhost:8000/api/farmer/loans/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 60000,
    "purpose": "equipment_purchase",
    "duration_months": 18,
    "description": "Updated request"
  }'
```

### Delete Pending Loan
```bash
curl -X DELETE http://localhost:8000/api/farmer/loans/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Integration Checklist

- [x] Frontend LoansView.vue created and styled
- [x] Backend LoanController updated with full CRUD
- [x] StoreLoanRequest form created with validation
- [x] Routes updated to use apiResource
- [x] Status badges with appropriate colors
- [x] Filtering and search implemented
- [x] Pagination implemented (15 per page)
- [x] Error handling and validation
- [x] Responsive design
- [x] Repayment progress tracking

---

## Testing Checklist

Before deployment, test:

- [ ] Create new loan application
- [ ] View loan in list with correct status
- [ ] Update pending loan
- [ ] Delete pending loan
- [ ] Try to update/delete non-pending loan (should fail)
- [ ] Search loans by number
- [ ] Filter loans by status
- [ ] View loan details
- [ ] Check repayment progress display
- [ ] Verify validation errors show correctly
- [ ] Test with large amounts
- [ ] Test with long descriptions
- [ ] Verify pagination (15 per page)
- [ ] Test responsive layout on mobile
- [ ] Check color-coded status badges

---

## Optional Enhancements

1. **Payment Records** - Add ability to record and view individual repayments
2. **Loan Documents** - Allow farmers to upload supporting documents
3. **Loan Offers** - Display pre-approved loan offers based on farm profile
4. **Repayment Schedule** - Calculate and display monthly payment amounts
5. **Interest Calculator** - Show loan calculator for different amounts/durations
6. **Loan History** - Archive completed/rejected loans
7. **Notifications** - Alert when approval status changes
8. **SMS Reminders** - Send reminders for upcoming payments
9. **Auto-repayment** - Set up automatic payment deductions
10. **Loan Refinancing** - Allow refinancing of existing loans

---

## Files Created/Modified

### Created
- `frontend/src/views/farmer/LoansView.vue` - Loan management component
- `backend/app/Http/Requests/Farmer/StoreLoanRequest.php` - Form validation

### Modified
- `backend/app/Http/Controllers/Api/Farmer/LoanController.php` - Enhanced CRUD
- `backend/routes/api.php` - Updated loan routes

---

## Database Considerations

The Loan model uses:
- **loan_number** column (string, unique identifier)
- **status** column (enum/string, tracks state)
- **amount_paid** and **remaining_balance** (for tracking repayment)
- **approved_at**, **disbursed_at** (for tracking timeline)

Existing migrations should have already created these columns. If not, ensure database schema matches model attributes.

---

## Performance Notes

- Pagination set to 15 loans per page
- Relationships eager-loaded to prevent N+1 queries
- Status filtering done at database level
- Search uses LIKE on loan_number and purpose
- Indexes recommended on: farmer_id, status, created_at

---

**Task Status**: ✅ READY FOR TESTING

**All 7 Farmer Dashboard Features Complete!**

1. ✅ Add Farm Button
2. ✅ Plant Crop Button
3. ✅ Record Harvest Button
4. ✅ Add Product Button
5. ✅ New Transport Request Button
6. ✅ Request Consultation Button
7. ✅ Apply for Loan Button ← THIS TASK

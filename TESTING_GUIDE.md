# AgriTech Platform - Testing Guide

## Frontend & Backend Integration Testing

### Prerequisites
1. ✅ Backend database migration must be run: `php artisan migrate`
2. ✅ Frontend is served on `http://localhost:5173` (or configured port)
3. ✅ Backend API is served on `http://localhost:8000` (or configured port)
4. ✅ User authenticated with Farmer role
5. ✅ Bearer token obtained after login

---

## Part 1: Consultations Testing

### 1.1 Test Getting Experts List

**Endpoint**: `GET /api/experts`
**Authentication**: Not required (public endpoint)
**Expected Response**: 200 OK
```json
{
  "data": [
    {
      "id": 1,
      "name": "Dr. John Smith",
      "email": "john@example.com",
      "phone": "1234567890",
      "specialization": "Crop Management",
      "expertise_area": "Maize & Wheat",
      "years_experience": 10
    }
  ]
}
```

**Frontend Test**:
- Navigate to ConsultationsView.vue
- Verify expert dropdown populates
- Experts should appear in the "Select Expert" field

---

### 1.2 Test Creating Consultation Request

**Endpoint**: `POST /api/farmer/consultations`
**Authentication**: Required (Farmer role)
**Request Body**:
```json
{
  "expert_id": 1,
  "title": "Crop Disease Diagnosis",
  "description": "My maize plants show brown spots. What could be the issue?",
  "consultation_type": "pest",
  "priority": "high",
  "budget": 50,
  "preferred_date": "2026-08-15"
}
```

**Expected Response**: 201 Created
```json
{
  "message": "Consultation request created successfully",
  "data": {
    "id": 1,
    "farmer_id": 5,
    "expert_id": 1,
    "question": "Crop Disease Diagnosis",
    "answer": "My maize plants show brown spots. What could be the issue?",
    "category": "pest",
    "status": "pending",
    "is_public": false,
    "created_at": "2026-08-11T10:30:00Z",
    "expert": { /* expert data */ },
    "messages": []
  }
}
```

**Frontend Test**:
1. Click "Request Consultation" button
2. Fill form:
   - Expert: Select from dropdown
   - Title: "Test Consultation"
   - Type: "Pest Control"
   - Priority: "High"
   - Description: "Test description"
   - Budget: "100"
   - Date: Select future date
3. Click "Request Consultation"
4. Verify success message appears
5. Verify consultation appears in list with correct status

---

### 1.3 Test Getting All Consultations

**Endpoint**: `GET /api/farmer/consultations`
**Authentication**: Required (Farmer role)
**Expected Response**: 200 OK
```json
{
  "data": [
    {
      "id": 1,
      "farmer_id": 5,
      "expert_id": 1,
      "question": "Crop Disease Diagnosis",
      "answer": "My maize plants show brown spots...",
      "category": "pest",
      "status": "pending",
      "created_at": "2026-08-11T10:30:00Z",
      "messages": [],
      "expert": { /* expert data */ }
    }
  ],
  "pagination": {
    "total": 1,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

**Frontend Test**:
1. Navigate to ConsultationsView
2. Verify page loads
3. Check that created consultation appears in the list
4. Verify stats are correct:
   - Total Requests shows 1
   - Pending Response shows 1
   - Status badge shows "Pending"

---

### 1.4 Test Updating Consultation

**Endpoint**: `PUT /api/farmer/consultations/{id}`
**Authentication**: Required (Farmer role)
**Request Body**: Same as create
**Expected Response**: 200 OK with updated data

**Frontend Test**:
1. In consultation card, click "Edit" button (only visible if status is pending)
2. Modify form fields
3. Click "Update Request"
4. Verify success message
5. Verify changes reflected in list

---

### 1.5 Test Filtering Consultations

**Frontend Test**:
1. Create multiple consultations with different types and statuses
2. Test search box: Filter by title/description
3. Test type filter: Filter by crop/soil/pest/etc.
4. Test status filter: Filter by pending/responded/resolved
5. Verify correct results displayed

---

### 1.6 Test Viewing Consultation Details

**Endpoint**: `GET /api/farmer/consultations/{id}`
**Authentication**: Required (Farmer role)
**Expected Response**: 200 OK

**Frontend Test**:
1. Click "View" button on consultation card
2. Modal opens showing:
   - Consultation type
   - Priority level
   - Status badge
   - Expert information
   - Full description
   - Messages section (empty initially)
3. Close modal

---

### 1.7 Test Deleting Consultation

**Endpoint**: `DELETE /api/farmer/consultations/{id}`
**Authentication**: Required (Farmer role)
**Expected Response**: 200 OK
```json
{
  "message": "Consultation deleted successfully"
}
```

**Frontend Test**:
1. Click "Delete" button on consultation card
2. Confirm deletion
3. Verify consultation removed from list
4. Verify stats updated

---

## Part 2: Transport Requests Testing

### 2.1 Test Creating Transport Request

**Endpoint**: `POST /api/farmer/transport-requests`
**Authentication**: Required (Farmer role)
**Request Body**:
```json
{
  "product_name": "Organic Maize",
  "quantity": 100,
  "unit": "bags",
  "pickup_location": "Addis Ababa, Farm Road 5",
  "delivery_location": "Dire Dawa Market",
  "preferred_date": "2026-08-15",
  "notes": "Handle with care, fragile packaging"
}
```

**Expected Response**: 201 Created
```json
{
  "message": "Transport request created successfully",
  "data": {
    "id": 1,
    "farmer_id": 5,
    "product_name": "Organic Maize",
    "quantity": 100,
    "unit": "bags",
    "pickup_location": "Addis Ababa, Farm Road 5",
    "delivery_location": "Dire Dawa Market",
    "pickup_date": "2026-08-15",
    "status": "pending",
    "created_at": "2026-08-11T10:30:00Z"
  }
}
```

**Frontend Test**:
1. Click "New Request" button
2. Fill form:
   - Product Name: "Maize"
   - Quantity: "50"
   - Unit: "kg"
   - Pickup Location: "My Farm"
   - Delivery Location: "Central Market"
   - Preferred Date: Future date
   - Special Instructions: "Keep cool"
3. Click "Create Request"
4. Verify request appears in table with "Pending" status

---

### 2.2 Test Getting All Transport Requests

**Endpoint**: `GET /api/farmer/transport-requests`
**Authentication**: Required (Farmer role)
**Expected Response**: 200 OK with paginated requests

**Frontend Test**:
1. Navigate to TransportView
2. Verify page loads
3. Check that transport table shows:
   - Request ID
   - Product name
   - Quantity and unit
   - Pickup/Delivery locations
   - Status badge (color-coded)
   - Created date
   - Action buttons
4. Verify stats update correctly

---

### 2.3 Test Updating Transport Request

**Endpoint**: `PUT /api/farmer/transport-requests/{id}`
**Authentication**: Required (Farmer role)
**Expected Response**: 200 OK

**Frontend Test**:
1. Click "Edit" button on pending request
2. Modify details (quantity, locations, etc.)
3. Click "Update Request"
4. Verify changes reflected in table

---

### 2.4 Test Transport Status Filtering

**Frontend Test**:
1. Create requests with different statuses (if backend allows)
2. Use status filter dropdown
3. Test filters:
   - All Status
   - Pending
   - Assigned
   - In Transit
   - Delivered
   - Cancelled
4. Verify correct requests show for each filter

---

### 2.5 Test Search Functionality

**Frontend Test**:
1. Create multiple transport requests
2. Enter search text (product name, location, etc.)
3. Verify only matching requests display
4. Test various search terms

---

### 2.6 Test Tracking Transport Request

**Frontend Test**:
1. Click "Track" button (map icon) on any request
2. Tracking modal should show:
   - Product name
   - Quantity
   - From/To locations
   - Status with color badge
   - Created date
3. Modal displays current tracking info
4. Close modal

---

### 2.7 Test Deleting Transport Request

**Endpoint**: `DELETE /api/farmer/transport-requests/{id}`
**Authentication**: Required (Farmer role)
**Expected Response**: 200 OK

**Frontend Test**:
1. Click "Delete" button on request
2. Confirm deletion
3. Verify request removed from table
4. Verify stats updated

---

## Part 3: Error Handling Tests

### 3.1 Test Missing Required Fields

**Test**: Submit form without required fields
**Expected**: Validation error messages appear above each field

**Frontend Test**:
1. Click "Request Consultation"
2. Leave required fields empty
3. Submit form
4. Verify error: "This field is required" or similar
5. Test same for Transport requests

---

### 3.2 Test Invalid Dates

**Test**: Submit with date in past
**Expected**: Validation error

**Frontend Test**:
1. Try to select past date in date picker
2. Should be disabled or show error
3. Select valid future date - should work

---

### 3.3 Test Unauthorized Access

**Test**: Try to access another farmer's consultation/transport request
**Expected**: 404 Not Found or 403 Forbidden

(Backend should handle this - frontend shouldn't show other farmer's data anyway)

---

### 3.4 Test Server Error Handling

**Test**: Intentionally cause server error (if possible)
**Expected**: User-friendly error message appears

**Frontend Test**:
1. Check for error toast/modal when requests fail
2. Verify error message is clear and helpful

---

## Part 4: UI/UX Tests

### 4.1 Consultations Page

**Visual Test**:
- [ ] Stats cards display with correct icons and values
- [ ] Search box functional and responsive
- [ ] Type dropdown works with all options
- [ ] Status dropdown works with all options
- [ ] Consultation cards show all information clearly
- [ ] Priority badges show correct colors:
  - Low: Green
  - Medium: Yellow
  - High: Red
  - Urgent: Dark Red
- [ ] Status badges show correct colors:
  - Pending: Yellow
  - Responded: Blue
  - Resolved: Green
  - Closed: Gray
- [ ] Buttons are properly aligned and spaced
- [ ] Modal forms are clean and organized
- [ ] Empty state displays helpful message
- [ ] Loading spinner appears when fetching

**Interaction Test**:
- [ ] Buttons respond quickly
- [ ] Form validation errors clear and helpful
- [ ] Modals open/close smoothly
- [ ] Can scroll through long lists
- [ ] Responsive on mobile (if applicable)

---

### 4.2 Transport Requests Page

**Visual Test**:
- [ ] Stats cards show correct counts and icons
- [ ] Table headers are clear and aligned
- [ ] Table rows display all information
- [ ] Status badges show correct colors
- [ ] Action icons (track, edit, delete) visible and accessible
- [ ] Search input responsive
- [ ] Status filter dropdown works
- [ ] Modal forms are well-organized
- [ ] Empty state displays helpful message

**Interaction Test**:
- [ ] All buttons respond quickly
- [ ] Form validation provides clear feedback
- [ ] Modals open/close smoothly
- [ ] Tracking modal shows accurate information
- [ ] Table scrolls properly if wide
- [ ] Mobile responsive layout works

---

## Part 5: Performance Tests

### 5.1 Page Load Time

**Test**: Measure load time for:
- ConsultationsView on load
- TransportView on load

**Expected**: Should load within 2-3 seconds with moderate data

### 5.2 List Performance

**Test**: With 50+ items in list
- [ ] Search filters quickly
- [ ] Status filter applies instantly
- [ ] Pagination works (if implemented)
- [ ] No lag when scrolling

### 5.3 Form Submission

**Test**: Submit large form data
- [ ] Form submits within 2-3 seconds
- [ ] Loading state shows during submission
- [ ] Success/error feedback appears promptly

---

## Test Data Setup

### Create Test Users

```sql
-- Expert user (for consultations)
INSERT INTO users (name, email, password, phone, created_at, updated_at) 
VALUES ('Dr. Agricultural Expert', 'expert@agritech.com', password('secure123'), '251911234567', NOW(), NOW());

-- Assign expert role to this user
-- (Use appropriate role assignment method in your system)
```

### Create Test Farmers

```sql
-- Farmer user
INSERT INTO users (name, email, password, phone, created_at, updated_at) 
VALUES ('John Farmer', 'farmer@agritech.com', password('secure123'), '251922345678', NOW(), NOW());

-- Create farmer profile
INSERT INTO farmers (user_id, region, zone, woreda, created_at, updated_at)
VALUES (last_user_id, 'Oromia', 'East Harar', 'Adama', NOW(), NOW());
```

---

## Checklist for Complete Testing

### Consultations
- [ ] List all consultations
- [ ] Create new consultation
- [ ] View consultation details
- [ ] Update consultation
- [ ] Delete consultation
- [ ] Filter by type
- [ ] Filter by status
- [ ] Search consultations
- [ ] Expert dropdown populated
- [ ] Stats accurate

### Transport
- [ ] List all requests
- [ ] Create new request
- [ ] View request details
- [ ] Update request
- [ ] Delete request
- [ ] Filter by status
- [ ] Search requests
- [ ] Track request
- [ ] Stats accurate

### Errors
- [ ] Validation errors show
- [ ] Network errors handled
- [ ] 404 errors handled
- [ ] 500 errors handled
- [ ] Unauthorized access denied

### UI/UX
- [ ] Colors consistent
- [ ] Badges display correctly
- [ ] Icons visible
- [ ] Modals smooth
- [ ] Responsive layout
- [ ] Loading states work

---

## Common Issues & Solutions

### Issue: Expert dropdown is empty
**Solution**: Ensure users with 'expert' role exist in database

### Issue: 404 errors on API calls
**Solution**: Verify routes are registered and middleware applied

### Issue: Validations not showing
**Solution**: Check that formErrors object is properly bound in template

### Issue: Consultations not loading
**Solution**: Verify farmer_id relationship and migration ran successfully

### Issue: Status badges wrong colors
**Solution**: Check CSS class names match status values

---

## Browser Console Debugging

When testing, check browser console for:
- [ ] No JavaScript errors
- [ ] API calls showing in Network tab with 200/201 status
- [ ] Request/response data visible in Network tab
- [ ] No CORS errors
- [ ] No console warnings

---

**Date**: August 11, 2026
**Status**: ✅ Ready for testing

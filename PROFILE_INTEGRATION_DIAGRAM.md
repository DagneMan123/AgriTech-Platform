# Farmer Profile Integration - Visual Diagram

## 🗺️ Navigation Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    FARMER INTERFACE                          │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────┐      ┌────────────────────────────┐  │
│  │ FARMER SIDEBAR   │      │   MAIN CONTENT AREA        │  │
│  │ (Fixed 260px)    │      │                            │  │
│  ├──────────────────┤      │  Displays Current View     │  │
│  │ Dashboard        │──────┤                            │  │
│  │ My Farms         │      │  Page Header               │  │
│  │ My Crops         │      │  ┌──────────────────────┐  │  │
│  │ Harvests         │      │  │ Content Sections     │  │  │
│  │ Products         │      │  │ (Based on Route)     │  │  │
│  │ Orders           │      │  └──────────────────────┘  │  │
│  │ Buy Farm Inputs  │      │                            │  │
│  │ Transport        │      │                            │  │
│  │ Weather          │      │                            │  │
│  │ Market Prices    │      │                            │  │
│  │ Consultations    │      │                            │  │
│  │ Loans            │      │                            │  │
│  │ Reports          │      │                            │  │
│  ├──────────────────┤      │                            │  │
│  │ ▶ PROFILE   ◄────┼──────┤  ┌──────────────────────┐  │  │
│  │ Logout           │      │  │  ProfileView.vue     │  │  │
│  └──────────────────┘      │  │                      │  │  │
│                            │  │ • Avatar Display     │  │  │
│                            │  │ • Personal Info      │  │  │
│                            │  │ • Farm Details       │  │  │
│                            │  │ • Contact Info       │  │  │
│                            │  │ • Bank Info          │  │  │
│                            │  │ • Settings           │  │  │
│                            │  │ • Danger Zone        │  │  │
│                            │  └──────────────────────┘  │  │
│                            │                            │  │
│                            └────────────────────────────┘  │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 Route Registration Flow

```
┌─ frontend/src/router/index.ts
│
├─ Import FarmerProfileView
│  └─ from '@/views/farmer/ProfileView.vue'
│
└─ Register Route
   │
   ├─ Path: 'profile'
   ├─ Name: 'farmer-profile'
   ├─ Component: FarmerProfileView
   └─ Meta:
      ├─ requiresAuth: true ────────┐
      ├─ requiredRole: 'farmer' ────┤── Protection
      └─ title: 'My Profile' ───────┘
```

---

## 🔐 Access Control Flow

```
                    USER TRIES TO ACCESS
                    /farmer/profile
                          │
                          ▼
                  ┌──────────────┐
                  │ Is Logged In?│
                  └──┬───────┬───┘
                     │       │
                  YES│       │NO
                     │       │
                     ▼       ▼
              ┌─────────┐  ┌──────────────┐
              │Has Role │  │ REDIRECT     │
              │Farmer?  │  │ to /login    │
              └┬─────┬──┘  └──────────────┘
                │   │
             YES│   │NO
                │   │
                ▼   ▼
           ┌─────────────────────────┐
           │ REDIRECT to user's      │
           │ appropriate dashboard   │
           └─────────────────────────┘
                │
                │ (only if has role)
                ▼
        ┌─────────────────────┐
        │ LOAD PROFILE PAGE   │
        │                     │
        │ ProfileView.vue     │
        └─────────────────────┘
```

---

## 📝 Component Structure

```
ProfileView.vue
│
├── Profile Header Section
│   ├── Avatar Display
│   ├── User Name
│   ├── Role Badge
│   ├── Email Display
│   └── Edit Profile Button
│
├── Personal Information Section
│   ├── Full Name (Editable)
│   ├── Email (Editable)
│   ├── Phone (Editable)
│   └── DOB (Editable)
│
├── Farm Information Section
│   ├── Farm Name (Editable)
│   ├── Location (Editable)
│   ├── Farm Size (Editable)
│   └── Primary Crops (Editable)
│
├── Contact Information Section
│   ├── Address (Editable)
│   ├── City (Editable)
│   ├── State/Province (Editable)
│   └── Postal Code (Editable)
│
├── Bank Information Section
│   ├── Bank Name (Editable)
│   ├── Account Number (Masked & Editable)
│   └── Account Holder (Editable)
│
├── Account Settings Section
│   ├── Change Password Button
│   ├── 2FA Button
│   └── Privacy Settings Button
│
└── Danger Zone Section
    ├── Warning Title
    ├── Description
    └── Delete Account Button
```

---

## 🔄 Edit Mode Flow

```
┌─────────────────────────────────────────┐
│      PROFILE PAGE LOADS                 │
│      (View Mode - Default)              │
│                                         │
│  Data displayed as read-only text       │
│  "Edit Profile" button visible          │
└────────────┬────────────────────────────┘
             │
             │ User clicks "Edit Profile"
             │
             ▼
┌─────────────────────────────────────────┐
│      EDIT MODE ACTIVATED                │
│                                         │
│  All fields become input elements       │
│  "Edit Profile" button changes to:      │
│  - "Cancel" button                      │
│  - "Save Changes" button visible        │
└────────────┬─────────┬──────────────────┘
             │         │
        SAVE │         │ CANCEL
             │         │
             ▼         ▼
    ┌─────────────┐  ┌──────────────────┐
    │Send Data to │  │Reset Form Data   │
    │API Endpoint │  │Exit Edit Mode    │
    │Update State │  │Return to View    │
    └─────────────┘  └──────────────────┘
             │         │
             └────┬────┘
                  │
                  ▼
       ┌──────────────────────┐
       │    VIEW MODE         │
       │   (Updated Data)     │
       └──────────────────────┘
```

---

## 🌐 API Integration Points

```
ProfileView.vue
│
├─ onMounted()
│  └─ fetchProfileData()
│     │
│     └─ API Call:
│        GET /api/farmer/profile
│        Headers: { Authorization: Bearer {token} }
│        Response: { Profile Data }
│
└─ saveProfile()
   └─ API Call:
      PUT /api/farmer/profile
      Headers: { 
         Authorization: Bearer {token},
         Content-Type: application/json
      }
      Body: { Form Data }
      Response: { Updated Profile Data }
```

---

## 📱 Responsive Design Breakpoints

```
DESKTOP (> 1024px)
┌──────────┬──────────────────────────────┐
│ SIDEBAR  │  4-Column Grid Layout         │
│ 260px    │  • Section 1 | Section 2     │
│          │  • Section 3 | Section 4     │
│          │  • Section 5 | Section 6     │
│          │  • Section 7 | Full Width    │
└──────────┴──────────────────────────────┘

TABLET (768px - 1024px)
┌──────────┬──────────────────────────┐
│ SIDEBAR  │  2-Column Grid Layout     │
│ 260px    │  • Section 1 | Section 2 │
│          │  • Section 3 | Section 4 │
│          │  • Full Width Sections   │
└──────────┴──────────────────────────┘

MOBILE (< 768px)
┌──────────────────┐
│ NO SIDEBAR       │
│ (Menu Button)    │
├──────────────────┤
│ 1-Column Layout  │
│ • Section 1      │
│ • Section 2      │
│ • Section 3      │
│ • Section 4      │
│ • Section 5      │
│ • Section 6      │
│ • Section 7      │
└──────────────────┘
```

---

## 🎨 Color Scheme

```
PRIMARY COLORS (Farmer Role)
┌─────────────────────────────────────────┐
│  Green: #10b981                          │
│  └─ Used for buttons, active states     │
│     hover effects, accents              │
└─────────────────────────────────────────┘

SECONDARY COLORS
┌─────────────────────────────────────────┐
│  Gray: #6b7280                           │
│  └─ Used for text, borders, labels     │
│                                          │
│  Light Gray: #f3f4f6, #f9fafb           │
│  └─ Used for backgrounds, sections     │
└─────────────────────────────────────────┘

STATUS COLORS
┌─────────────────────────────────────────┐
│  Red: #ef4444                            │
│  └─ Used for danger zone, delete       │
│                                          │
│  Green: #10b981                          │
│  └─ Used for success, active states    │
└─────────────────────────────────────────┘
```

---

## 🔒 Security Layers

```
Layer 1: Route Protection
└─ requiresAuth: true
   └─ Checks if user is logged in
      └─ Redirects to /login if not

Layer 2: Role-Based Access
└─ requiredRole: 'farmer'
   └─ Checks if user has farmer role
      └─ Redirects to appropriate dashboard

Layer 3: API Authentication
└─ Bearer Token in Headers
   └─ Authorization: Bearer {token}
      └─ Validates token on backend

Layer 4: Data Security
└─ Bank account masked
   └─ Shows only last 4 digits
      └─ Full number only in edit mode
```

---

## 📊 State Management

```
Component State (Vue 3 Composition API)
│
├─ editMode: ref(false)
│  └─ Controls edit/view mode toggle
│
├─ profileData: ref({...})
│  └─ Stores fetched profile information
│
└─ formData: reactive({...})
   └─ Stores form input values for editing
```

---

## 🎯 User Journey

```
START: Login Page
  │
  ▼
Farmer Dashboard
  │
  ▼
  └─ Click "Profile" in Sidebar
       │
       ▼
    Load Profile Page
    ├─ Fetch profile data from API
    ├─ Display profile information
    └─ Show edit button
       │
       ▼
    View Profile Data
    ├─ Personal Information ✓
    ├─ Farm Information ✓
    ├─ Contact Information ✓
    ├─ Bank Information ✓
    └─ Account Settings ✓
       │
       ├─ User clicks "Edit Profile"
       │  │
       │  ▼
       │  Edit Mode
       │  ├─ All fields become editable
       │  └─ Show Save/Cancel buttons
       │     │
       │     ├─ Save → Update Profile ✓
       │     │
       │     └─ Cancel → Return to View ✓
       │
       └─ User navigates away
          └─ Return to previous page ✓
```

---

## ✅ Integration Checklist

```
[✓] Profile page component created
[✓] Router import added
[✓] Route registered in router
[✓] Route metadata configured
[✓] Sidebar link verified
[✓] Component styling complete
[✓] Edit mode functionality implemented
[✓] Form structure created
[✓] API integration structure prepared
[✓] Security measures implemented
[✓] Responsive design tested
[✓] Error handling prepared
[✓] Documentation completed
```

---

**Diagram Status**: Complete and Ready ✅  
**Last Updated**: August 10, 2026

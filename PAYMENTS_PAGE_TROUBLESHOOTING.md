# Payments Page Navigation Troubleshooting Guide

## Quick Diagnostic Steps

### 1. **Verify Browser Console for Errors**
- Open your browser's Developer Tools (F12)
- Click on the "Console" tab
- Click the Payments link in the sidebar
- **Look for any JavaScript errors** - they will show in red
- **You should see**: `"PaymentsView mounted successfully"` if the component loads

### 2. **Check the Route is Correct**
The payments route should be:
- **Path**: `/farmer/payments`
- **Route Name**: `farmer-payments`
- **Located in**: `frontend/src/router/index.ts` (Line 286-291)

### 3. **Verify Sidebar Link**
The sidebar link should be:
- **File**: `frontend/src/components/Sidebar/FarmerSidebar.vue` (Line 213)
- **Link**: `to="/farmer/payments"`
- **Section**: Financial Services section

### 4. **Check Network Tab**
- Open Developer Tools → Network tab
- Click Payments link
- **Expected**: See a network request for loading the component (might appear as `/farmer/payments` or similar)
- **Check**: Is there a 404 error? Is the component JavaScript loading?

### 5. **Test Direct URL Navigation**
- Try manually typing in the browser: `http://localhost:5173/farmer/payments`
- Does it load?
- If yes: The route works, but sidebar link has an issue
- If no: The route itself is broken

### 6. **Check for Navigation Guards**
The router has auth checks at `frontend/src/router/index.ts` (Lines 567-592)
- Are you logged in as a farmer?
- Check localStorage for `userRole` - it should be `'farmer'`
- Open DevTools Console and run: `localStorage.getItem('userRole')`

## Root Cause Analysis

### If Page Opens via Direct URL but NOT via Sidebar Click:
**Problem**: Sidebar link is not working
**Solution**: 
1. Check if FarmerSidebar is being imported in PaymentsView
2. Verify no JavaScript error is preventing the click handler

### If Page Doesn't Open Either Way:
**Problem**: Route or component issue
**Possible Solutions**:
1. Check for JavaScript errors in Console
2. Verify the route path matches exactly: `/farmer/payments` (no extra slashes)
3. Check that user role is `'farmer'`
4. Check that authentication is valid

### If Component Loads but Nothing Displays:
**Problem**: Component renders but content is blank
**Solution**:
1. Check if CSS is hiding content (check `payments-layout` class styling)
2. Check if data is not initializing (look for errors with loading state)
3. Verify `useTheme()` composable is working

## Files to Verify

✅ **Component**: `frontend/src/views/farmer/PaymentsView.vue`
- Line 1-2: Template with dark mode binding
- Line 361: `useTheme` import
- Line 370: `const { isDark, isLight } = useTheme()`
- Line 550+: Component mounted with console.log

✅ **Router**: `frontend/src/router/index.ts`
- Line 53: Component import
- Line 286-291: Route definition

✅ **Sidebar**: `frontend/src/components/Sidebar/FarmerSidebar.vue`
- Line 213: Payment link to="/farmer/payments"

## Success Criteria

When working properly:
1. ✅ Click Payments link in sidebar
2. ✅ Page URL changes to `/farmer/payments`
3. ✅ PaymentsView component renders
4. ✅ Console shows "PaymentsView mounted successfully"
5. ✅ Dark mode CSS applies correctly
6. ✅ All sections (Payment Methods, Transactions, Schedules) display

## Quick Fix Checklist

- [ ] Clear browser cache (Ctrl+Shift+Delete)
- [ ] Hard refresh the page (Ctrl+Shift+R)
- [ ] Clear localStorage if needed: `localStorage.clear()`
- [ ] Rebuild/restart dev server if changes made
- [ ] Check no blocking network requests (Network tab)
- [ ] Verify auth token is still valid (should auto-refresh)

## If Still Not Working

Please run this in the browser console and share the output:
```javascript
// Check route exists
console.log('Routes:', router.getRoutes().map(r => r.path));

// Check current route
console.log('Current route:', router.currentRoute.value.name);

// Check auth
console.log('Auth:', authStore.isAuthenticated, authStore.userRole);

// Try navigating programmatically
router.push('/farmer/payments');
```

---

**Dark Mode Implementation Status**: ✅ COMPLETE
- All 400+ lines of dark mode CSS have been added
- Component properly bound with `:class="{ 'light': isLight, 'dark': isDark }"`
- useTheme composable integrated

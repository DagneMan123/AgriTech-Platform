# Theme Toggle Debug Guide

## Current Implementation Status

### ✅ Working Components

1. **Theme Store (`themeStore.ts`)**
   - Reads localStorage on init for theme preference
   - Has `toggleTheme()` function that flips `isDark` state
   - Watcher with `immediate: true` syncs changes to DOM and localStorage
   - `syncThemeToDom()` adds/removes `dark` class from `<html>` element

2. **HTML Pre-load Script (`index.html`)**
   - Runs before Vue mounts
   - Reads localStorage and applies `dark` class if needed
   - Prevents flash of unstyled content

3. **LoginView**
   - Has toggle button with `@click="toggleTheme"`
   - Uses `:deep(html.dark)` for all dark mode selectors
   - Light mode styles set as defaults (not dark)

4. **ForgotPasswordView**
   - No toggle button (correct)
   - Uses same `useThemeStore()`
   - Uses `:deep(html.dark)` for dark mode selectors
   - Responds to theme changes from LoginView

### 🔍 Testing Steps

**To test if toggle is working:**

1. Open login page in browser
2. Check DevTools Console:
   ```javascript
   // Check localStorage
   console.log(localStorage.getItem('theme-preference'))
   
   // Check if dark class is on html
   console.log(document.documentElement.classList.contains('dark'))
   ```

3. Click the theme toggle button (☀️ or 🌙 icon)
4. Verify:
   - ✓ localStorage value changes
   - ✓ `dark` class is added/removed from `<html>`
   - ✓ Page theme visibly changes
   - ✓ Icon changes (☀️ ↔️ 🌙)

5. Navigate to forgot password page
6. Verify:
   - ✓ Same theme as login page
   - ✓ No toggle button visible

7. Click toggle on login page
8. Verify:
   - ✓ Forgot page theme also changes
   - ✓ Login and Forgot pages stay in sync

### 📋 CSS Verification

Check that dark mode CSS is using `:deep()` correctly:

**In LoginView.vue `<style scoped>`:**
```css
:deep(html.dark) .auth-container {
  background-color: #0b0f17;
  color: #fff;
}
```

**In ForgotPasswordView.vue `<style scoped>`:**
```css
:deep(html.dark) .auth-container {
  background-color: #0b0f17;
  color: #fff;
}
```

### ⚠️ Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| Toggle not changing theme | `toggleTheme()` not called | Check button `@click` binding |
| Theme doesn't persist on refresh | localStorage not working | Check `theme-preference` key in DevTools |
| Only one page changes theme | Pages using different stores | Verify both use `useThemeStore()` |
| Dark mode styles not applied | `:deep()` missing | Add `:deep()` to all `html.dark` selectors |
| Flash on page load | Pre-load script not running | Check `index.html` script tag position |

### 🚀 If Everything Passes Tests

Then the implementation is working correctly. Both pages will share theme state, and clicking the toggle on login will affect the forgot password page immediately.

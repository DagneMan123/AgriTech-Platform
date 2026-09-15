# Quick Reference: Dark/Light Mode Theme

## What Should Work Now ✅

1. **Toggle Button**
   - Click 🌙/☀️ button on login page
   - Theme instantly changes
   - localStorage updates automatically
   - Icon flips to show current state

2. **Forgot Password Page**
   - No toggle button visible
   - When you toggle on login, forgot page changes too
   - Both pages always show same theme

3. **Page Refresh**
   - Light mode set → refresh → light mode stays
   - Dark mode set → refresh → dark mode stays
   - No white/dark flash on page load

4. **Browser Close/Reopen**
   - Close browser in dark mode
   - Open browser → dark mode loads
   - Close browser in light mode
   - Open browser → light mode loads

---

## Testing: 3-Step Quick Test

```
STEP 1: Light Mode Toggle
- Open login page (should be light by default)
- Click toggle button
- Page should turn dark
- Icon should change to ☀️

STEP 2: Forgot Page Sync
- Navigate to forgot password page
- Should be dark (same as login)
- Click "Back to Login"
- Click toggle again
- Both pages should switch to light

STEP 3: Persistence
- Keep page in dark mode
- Refresh browser (F5)
- Page should load in dark mode (no flash)
- Close browser completely
- Reopen browser and navigate to login
- Should still be in dark mode
```

---

## Storage Location

**Browser localStorage:**
- Key: `theme-preference`
- Values: `"dark"` or `"light"`
- Persists across: page refreshes, browser restarts, sessions

**Check with DevTools:**
```
Application tab → Local Storage → Select your domain → Look for 'theme-preference'
```

---

## CSS Architecture

| Scope | Light Mode | Dark Mode |
|-------|-----------|-----------|
| **LoginView** | Default styles | `:deep(html.dark) .class-name { ... }` |
| **ForgotPasswordView** | Default styles | `:deep(html.dark) .class-name { ... }` |
| **Global** | CSS Variables | Updated by `:deep()` selectors |

---

## Common Elements Styled

Both pages style these elements identically:

- `.auth-container` - Main wrapper
- `.form-section` - Right side form area
- `.form-card` - White/dark card
- `.form-title` - Heading text
- `.form-input` - Text input fields
- `.btn-sign-in` - Submit button
- `.error-box` - Error messages
- Input autofill colors (browser-specific)

---

## Why It Works

1. **Both pages import same store**: `useThemeStore()`
2. **Store is reactive**: Changes update globally
3. **Theme applied to `<html>` element**: CSS can target it with `:deep()`
4. **Immediate watcher**: Changes sync instantly
5. **localStorage**: Persists preference
6. **Pre-load script**: No flash on page load
7. **No system preference sync**: Only user controls theme

---

## If Something Doesn't Work

| Issue | Fix |
|-------|-----|
| Toggle doesn't change theme | Hard refresh (Ctrl+Shift+R) + clear cache |
| Only one page changes | Check both use `useThemeStore()` from same file |
| Theme doesn't save on refresh | Check browser localStorage is not disabled |
| Dark mode styles look wrong | Check `:deep()` combinator is present in CSS |
| Flash on page load | Check pre-load script in index.html runs first |

---

## Key Files

```
frontend/src/stores/themeStore.ts          ← Theme logic
frontend/src/views/auth/LoginView.vue      ← Has toggle button
frontend/src/views/auth/ForgotPasswordView.vue ← No toggle button
frontend/src/App.vue                       ← Initialize theme
frontend/index.html                        ← Pre-load script
```

---

**Last Updated:** 2026-09-15
**Status:** ✅ Complete and Ready to Test

# Dark Mode Implementation Guide

## Overview
A complete dark mode implementation has been added to the AgriTech Platform with smooth transitions between light and dark themes.

## Features Implemented

### 1. **Global Theme Toggle**
- Located in navbar and dashboard header for easy access
- Click the button to toggle between 🌙 Dark and ☀️ Light modes
- Theme preference is saved to localStorage
- Respects system theme preference on first visit

### 2. **Color Palette**

#### Light Mode
```
Background: #ffffff
Secondary BG: #f9fafb
Tertiary BG: #f3f4f6
Primary Text: #1f2937
Secondary Text: #6b7280
Borders: #e5e7eb
```

#### Dark Mode
```
Background: #0f1419 (Deep black)
Secondary BG: #1a1f2e
Tertiary BG: #252d3d
Primary Text: #f1f5f9 (Bright white)
Secondary Text: #cbd5e1
Borders: #334155
```

### 3. **Components Updated**

#### A. **DashboardLayout.vue**
- Theme toggle button in header
- Sidebar adapts to dark mode
- Dynamic background colors using CSS variables
- Smooth transitions on theme change

#### B. **AuthLayout.vue**
- Theme toggle in top-right corner
- Gradient background that adapts to theme
- Form container styling for dark mode

#### C. **ThemeToggle.vue**
- Improved button styling with animations
- Icons: 🌙 for dark mode, ☀️ for light mode
- Responsive design (hides text on mobile, shows icon only)

#### D. **Navbar.vue**
- Dynamic styling with CSS variables
- Theme toggle integration

### 4. **CSS Updates**

#### Main CSS (src/styles/main.css)
- Dark mode CSS variables
- Component styling for dark mode
- Form elements (input, textarea, select)
- Tables and interactive elements
- Scrollbar styling

#### Global CSS (src/assets/css/global.css)
- Typography dark mode support
- Link colors for dark mode
- Utility classes for dark mode
- Scrollbar professional styling

### 5. **Theme Store (themeStore.ts)**
Features:
- `isDark` - reactive boolean state
- `initializeTheme()` - loads theme from localStorage or system preference
- `toggleTheme()` - switches between light and dark
- `setTheme(dark)` - set specific theme
- `watchSystemTheme()` - monitors system theme changes
- `applyTheme()` - applies theme to DOM and localStorage

### 6. **App.vue**
- Initializes theme on mount
- Applies smooth transitions
- Container styling uses CSS variables

## Implementation Files

### Modified Files:
1. `frontend/src/styles/main.css` - Added comprehensive dark mode support
2. `frontend/src/assets/css/global.css` - Updated color palette
3. `frontend/src/layouts/DashboardLayout.vue` - Added theme toggle
4. `frontend/src/layouts/AuthLayout.vue` - Added theme toggle + styling
5. `frontend/src/components/Theme/ThemeToggle.vue` - Enhanced component
6. `frontend/src/components/layout/Navbar.vue` - Updated styling
7. `frontend/src/App.vue` - Updated transitions

### Existing Files (No Changes Needed):
- `frontend/src/stores/themeStore.ts` - Already properly implemented
- `frontend/src/composables/useTheme.ts` - Already functional

## How It Works

### Theme Initialization Flow:
1. App.vue mounts and calls `themeStore.initializeTheme()`
2. Store checks localStorage for saved preference
3. If no preference, checks system theme preference
4. Applies theme by adding/removing 'dark' class on `<html>` element
5. CSS variables automatically update via dark mode selectors

### Theme Toggle Flow:
1. User clicks ThemeToggle button
2. `toggleTheme()` flips the `isDark` state
3. `applyTheme()` updates DOM and localStorage
4. All CSS variables automatically apply via `html.dark` selector
5. Smooth transitions occur (0.3s duration)

## CSS Variable Usage

### In Vue Components (Scoped Styles):
```css
<style scoped>
.element {
  background-color: var(--bg-light);
  color: var(--text-light-primary);
  transition: background-color 0.3s ease;
}

html.dark .element {
  background-color: var(--bg-dark);
  color: var(--text-dark-primary);
}
</style>
```

### In Global CSS:
```css
html.dark body {
  background-color: var(--bg-dark);
  color: var(--text-dark-primary);
}

html.dark .card {
  background-color: var(--bg-dark-secondary);
  border-color: var(--border-dark);
}
```

## Browser Support

- ✅ Chrome/Edge 49+
- ✅ Firefox 15+
- ✅ Safari 9.1+
- ✅ All modern mobile browsers
- ✅ System theme preference (prefers-color-scheme)

## Future Enhancements

1. **Auto-switching**: Option to auto-switch at sunset/sunrise
2. **Custom themes**: Allow users to choose accent colors
3. **Per-page themes**: Different themes for different sections
4. **Theme scheduler**: Set specific times for theme changes
5. **Accessibility**: Ensure WCAG compliance for dark mode contrast

## Testing Checklist

- [ ] Theme toggle works on all pages
- [ ] Theme persists after page refresh
- [ ] Theme respects system preference
- [ ] Smooth transitions on toggle
- [ ] All form elements styled correctly
- [ ] All text readable in both modes
- [ ] Images/icons visible in both modes
- [ ] Mobile responsive works in dark mode
- [ ] No console errors on theme change

## Troubleshooting

### Theme not saving?
Check browser localStorage is enabled and no private/incognito mode.

### Styles not applying?
Ensure `main.css` and `global.css` are imported in App.vue or main.ts.

### Performance issues?
Transition time can be reduced from 0.3s to 0.15s if needed.

## Support

For dark mode related issues, check:
1. `themeStore.ts` - State management
2. `main.css` - CSS variables
3. `global.css` - Component styling
4. ThemeToggle component - Button functionality

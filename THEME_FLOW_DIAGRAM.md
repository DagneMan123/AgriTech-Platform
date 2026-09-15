# Theme Toggle Flow Diagrams

## User Click → Theme Change Flow

```
USER CLICKS TOGGLE BUTTON ON LOGIN PAGE
            ↓
    toggleTheme() called
            ↓
    isDark.value flips
    (true → false or false → true)
            ↓
    Watcher detects change
    (watch with immediate: true)
            ↓
    ┌─────────────────────────────────┐
    │  syncThemeToDom()               │
    │  Adds/removes 'dark' class      │
    │  from document.documentElement  │
    └─────────────────────────────────┘
            ↓
    ┌─────────────────────────────────┐
    │  saveTheme()                    │
    │  Sets localStorage['theme-preference']
    │  to 'dark' or 'light'           │
    └─────────────────────────────────┘
            ↓
    ┌─────────────────────────────────┐
    │  CSS RULES APPLY                │
    │                                 │
    │  If dark mode on:               │
    │  :deep(html.dark) .auth-container {
    │    background: #0b0f17;         │
    │  }                              │
    │                                 │
    │  If light mode on:              │
    │  .auth-container {              │
    │    background: #f8fafc;         │
    │  }                              │
    └─────────────────────────────────┘
            ↓
    PAGE THEME CHANGES VISUALLY
    TEXT AND BACKGROUNDS UPDATE
    ICON CHANGES (🌙 ↔ ☀️)
```

---

## Page Load → Apply Stored Theme Flow

```
USER OPENS BROWSER / NAVIGATES TO LOGIN PAGE
            ↓
    index.html LOADS
            ↓
    ┌─────────────────────────────────────────────┐
    │  PRE-LOAD SCRIPT RUNS (BEFORE VUE MOUNTS)  │
    │                                             │
    │  1. Read localStorage['theme-preference']  │
    │  2. If value === 'dark':                   │
    │     document.documentElement.classList    │
    │     .add('dark')                           │
    │  3. If value !== 'dark':                   │
    │     document.documentElement.classList    │
    │     .remove('dark')                        │
    └─────────────────────────────────────────────┘
            ↓
    <html class="dark"> OR <html> (no class)
            ↓
    VUE MOUNTS AND RENDERS
            ↓
    App.vue onMounted() calls:
    themeStore.initializeTheme()
            ↓
    ┌─────────────────────────────────┐
    │  initializeTheme()              │
    │  1. Get stored theme            │
    │  2. Update isDark if needed     │
    │  3. Sync to DOM (redundant but safe)
    └─────────────────────────────────┘
            ↓
    ┌─────────────────────────────────┐
    │  Watcher runs (immediate: true) │
    │  (Already synced, but ensures   │
    │   any changes are applied)      │
    └─────────────────────────────────┘
            ↓
    PAGE DISPLAYS IN CORRECT THEME
    (NO FLASH OF WRONG THEME)
```

---

## Multi-Page Sync Flow

```
SCENARIO: Toggle on Login Page → Both Pages Change

LOGIN PAGE TREE              FORGOT PAGE TREE
    ↓                            ↓
useThemeStore()   ←→ SHARED   useThemeStore()
    ↓             PINIA STORE    ↓
isDark = true                isDark = true
    ↓                            ↓
:deep(html.dark)           :deep(html.dark)
styles applied to           styles applied to
form-section            form-section
    ↓                            ↓
DARK THEME VISIBLE     DARK THEME VISIBLE


WHEN USER CLICKS TOGGLE:

isDark: true  (in shared store)
    ↓
isDark: false  (state changes globally)
    ↓
┌──────────────┬──────────────┐
│  LoginView   │ ForgotView   │
│  Watcher     │  Watcher     │
│  triggered   │  triggered   │
└──────────────┴──────────────┘
    ↓                ↓
Update styles      Update styles
to light mode      to light mode
    ↓                ↓
INSTANT SYNC: Both pages update at the same time!
```

---

## Theme Store State Management

```
┌─────────────────────────────────────────────────────┐
│               PINIA THEME STORE                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│  STATE:                                             │
│  isDark: ref(boolean)  ← Main reactive state       │
│                                                     │
│  METHODS:                                           │
│  ├─ toggleTheme()     ← Flip between true/false    │
│  ├─ setTheme(dark)    ← Set to specific value      │
│  ├─ initializeTheme() ← Load from localStorage    │
│  ├─ syncThemeToDom()  ← Add/remove 'dark' class   │
│  ├─ saveTheme()       ← Write to localStorage     │
│  └─ watchSystemTheme()← Disabled (not used)       │
│                                                     │
│  WATCHERS:                                          │
│  watch(isDark, () => {                             │
│    syncThemeToDom()   ← Update DOM                 │
│    saveTheme()        ← Update localStorage        │
│  }, { immediate: true })                           │
│                                                     │
└─────────────────────────────────────────────────────┘
         ↑              ↑              ↑
         │              │              │
   LoginView      ForgotPasswordView   App.vue
   (reads isDark) (reads isDark)   (initializes)
```

---

## localStorage Persistence Flow

```
SESSION 1: User sets Dark Mode
┌─────────────────────────────────┐
│  User clicks toggle             │
│  isDark = true                  │
│  saveTheme() called             │
│  localStorage['theme-preference']
│  = 'dark'                       │
│  Browser closes                 │
└─────────────────────────────────┘

STORAGE (Persists on disk)
{
  'theme-preference': 'dark'
}

SESSION 2: User opens browser again
┌─────────────────────────────────┐
│  Browser opens to login page    │
│  Pre-load script runs           │
│  Reads localStorage['theme-    │
│  preference'] = 'dark'          │
│  Adds 'dark' class to <html>    │
│  Vue mounts and renders         │
│  Dark theme loads               │
│  NO FLASH of light theme!       │
└─────────────────────────────────┘
```

---

## CSS Selector Scope Resolution

```
PROBLEM: Scoped styles can't reach global <html> element

<html class="dark">
  <body>
    <div id="app">
      <LoginView>
        <style scoped>
          html.dark .auth-container { ... }  ❌ Won't work!
        </style>
      </LoginView>
    </div>
  </body>
</html>

SOLUTION: Use :deep() combinator

<style scoped>
  :deep(html.dark) .auth-container { ... }  ✅ Works!
  ↑
  This tells Vue: "Pierce the scope boundary and
  apply this to the global html element"
</style>

RESULT:
html.dark selector reaches the global <html> element
and applies dark mode styles
```

---

## Component Hierarchy & State Flow

```
index.html
    ↓
    └─ Pre-load Script
       (Reads localStorage)
    ↓
main.ts
    ↓
App.vue
    ↓ (onMounted)
    └─ themeStore.initializeTheme()
       └─ Pinia Store Created
          ├─ isDark initialized from localStorage
          ├─ Watcher activated (immediate: true)
          └─ DOM synced
    ↓
<RouterView>
    ├─ LoginView
    │  ├─ useThemeStore() ← Same store instance
    │  ├─ Toggle button → toggleTheme()
    │  └─ CSS watches :deep(html.dark)
    │
    └─ ForgotPasswordView
       ├─ useThemeStore() ← Same store instance
       ├─ No toggle button
       └─ CSS watches :deep(html.dark)

When isDark changes in store:
Watchers in BOTH components react → Both re-render
```

---

## Timing Sequence Diagram

```
TIMELINE: Page Load with Dark Mode in localStorage

Time    Event                                   State
──────────────────────────────────────────────────────
0ms     Browser fetches index.html
        
5ms     Pre-load script executes              isDark = ? (not ready yet)
        localStorage['theme-preference']     Reads 'dark'
        Adds 'dark' class to <html>          HTML: class="dark"
        
10ms    index.html fully loaded
        
15ms    main.ts loads and runs
        
20ms    Vue app initializes                   isDark not yet initialized
        
25ms    App.vue component mounts              App.vue mounted()
        themeStore.initializeTheme() called
        isDark initialized from localStorage   isDark = true
        
30ms    Watcher detects change                Watcher triggered
        syncThemeToDom() called               (redundant, 'dark' class already there)
        
35ms    <RouterView> renders                  Login page shown
        
40ms    LoginView component mounts
        useThemeStore() called
        Accesses isDark = true
        
45ms    Page fully renders with dark theme    ✅ User sees dark theme
        (NO FLASH OF LIGHT THEME!)


COMPARISON: Without Pre-load Script
──────────────────────────────────────────────────────
Time    Event
──────────────────────────────────────────────────────
0ms     Browser fetches and loads index.html
        ⚠️  No pre-load script!
        HTML shows as default (no 'dark' class)
        
10ms    Page briefly shows LIGHT THEME to user
        ❌ FLASH! (white background, dark text hard to read in dark)
        
15ms    main.ts loads
        
20ms    Vue app initializes
        
25ms    isDark initialized from localStorage
        Watcher adds 'dark' class to <html>
        
30ms    Dark theme CSS applies
        Page switches to DARK THEME
        ❌ User sees jarring flash/transition
```

---

This flow ensures:
- ✅ No flash on page load (pre-load script applies theme first)
- ✅ Instant toggle (watcher syncs immediately)
- ✅ Both pages in sync (shared Pinia store)
- ✅ Theme persists (localStorage)
- ✅ Correct CSS applied (`:deep()` combinator)

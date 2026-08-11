# AgriTech Reports Page - Design System Reference

## Quick Design Specifications

### Color System
```
Primary Green: #10b981
Primary Dark Green: #059669
Light Green Gradient: #d1fae5 → #a7f3d0
Very Light Green: #f0fdf4

Blue (Info): #3b82f6
Red (Alert): #dc2626
Yellow (Warning): #f59e0b
Gray (Neutral): #9ca3af, #e5e7eb, #f3f4f6
```

### Spacing System
```
Extra Small: 8px
Small: 12px
Base: 15px
Medium: 20px
Large: 25px
Extra Large: 30px
XXL: 40px
```

### Border Radius
```
Small: 8px
Medium: 10px
Large: 12px
```

### Shadow System
```
Subtle: 0 2px 4px rgba(0, 0, 0, 0.1)
Light: 0 4px 12px rgba(0, 0, 0, 0.08)
Medium: 0 8px 20px rgba(16, 185, 129, 0.08)
Deep: 0 10px 30px rgba(16, 185, 129, 0.15)
Dramatic: 0 15px 35px rgba(16, 185, 129, 0.2)
```

### Typography
```
Page Title: 32px, weight 700, letter-spacing -0.5px
Section Heading: 22px, weight 700, letter-spacing -0.3px
Card Title: 15-20px, weight 700
Body: 14-15px, weight 500
Label: 12-13px, weight 600
Caption: 11-12px, weight 500
```

### Transitions
```
Standard: all 0.3s cubic-bezier(0.4, 0, 0.2, 1)
Fast: all 0.2s cubic-bezier(0.4, 0, 0.2, 1)
Slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1)
```

### Hover Effects
```
Elevation: translateY(-2px to -8px) with shadow increase
Scale: scale(1.02)
Color: Gradient or darker shade
Shadow: Enhanced with increased blur and spread
```

### Button System

#### Primary Button
- Background: Linear gradient (Green)
- Padding: 12px 24px
- Font-weight: 700
- Text-transform: UPPERCASE
- Hover: TranslateY(-3px) + enhanced shadow
- Active: Apply gradient + shadow

#### Secondary Button (Export)
- Background: White
- Border: 2px solid (format-specific color)
- Padding: 14px 24px
- Hover: Gradient background + shadow

#### Period Button
- Background: White (inactive) / Gradient (active)
- Border: 2px solid
- Padding: 10px 20px
- Hover: Transform + shadow

### Card System

#### Stat Card
- Padding: 25px
- Border-radius: 12px
- Hover: TranslateY(-8px) + shadow increase
- Icon: 60x60px with gradient background

#### Trend Card
- Padding: 20px
- Border-radius: 12px
- Background: Gradient (Green)
- Hover: TranslateY(-8px) scale(1.02)

#### List Item
- Padding: 16px
- Border-radius: 10px
- Border: 2px solid
- Hover: TranslateX(4px) + color change

#### Metric Card
- Padding: 25px
- Border-radius: 12px
- Background: Gradient (Blue tones)
- Hover: TranslateY(-8px)

### Layout Grid

#### Desktop (1200px+)
- Stats Grid: repeat(auto-fit, minmax(220px, 1fr)), gap 20px
- Trends: repeat(auto-fit, minmax(160px, 1fr)), gap 15px
- Metrics: repeat(auto-fit, minmax(180px, 1fr)), gap 18px
- Content Grid: repeat(auto-fit, minmax(350px, 1fr)), gap 25px

#### Tablet (768px-1199px)
- Stats Grid: 2 columns, gap 15px
- Metrics Grid: 2 columns, gap 15px
- Content Grid: 1 column, gap 20px

#### Mobile (480px-767px)
- Stats Grid: 1 column
- All Grid: 1 column with responsive gaps
- Full-width buttons

### Table Styling

#### Header
- Background: Gradient (#f0fdf4 → #f0f9ff)
- Border-bottom: 3px solid #d1fae5
- Text: UPPERCASE, letter-spacing 0.3px
- Color: #10b981

#### Rows
- Padding: 15px 16px
- Hover: Gradient background with inset shadow
- Border-bottom: 1px solid #f0fdf4

## Component Integration

### Stat Cards Display
- Each card shows: Icon + Label + Value
- Icon: 60x60px with shadow
- Label: 13px uppercase
- Value: 26px bold gradient text

### Period Selector
- Label: 15px bold
- Buttons: 4 period options
- Generate Report button: Primary style
- Horizontal layout on desktop, vertical on mobile

### Summary Metrics
- 4 metric cards in grid
- Different icon colors (green, blue, yellow, red)
- Hover elevates and shows more shadow

### Data Table
- Scrollable container with rounded corners
- Header with gradient background
- Rows with hover highlighting
- Responsive font sizes

### Lists
- Farmer/Buyer lists with ranking
- Each item: Name + Orders + Amount
- Values show in green gradient

## Accessibility Features
- Sufficient color contrast (WCAG AA)
- Readable font sizes (min 12px)
- Clear focus states on buttons
- Semantic HTML structure
- Keyboard navigation support

## Performance Optimizations
- CSS Grid for efficient layouts
- GPU-accelerated transitions (transform/opacity)
- Minimal shadow calculations
- Scoped CSS (no global pollution)
- Responsive media queries
- Optimized hover states

## Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Development Notes
- All colors use hex values for consistency
- Shadows use rgba for transparency control
- Gradients use CSS linear-gradient
- Transitions use standard cubic-bezier timing
- Media queries follow mobile-first approach
- Flexible grid system adapts to content

---

**Reference Date**: August 11, 2026
**Design Version**: 1.0 Professional Enterprise
**Component**: Reports & Analytics Page

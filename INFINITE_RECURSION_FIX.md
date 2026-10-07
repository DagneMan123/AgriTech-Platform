# Infinite Recursion Fix - Login Error

## Problem
The "Maximum call stack size reached" error during login was caused by broken polymorphic relationships between models.

## Root Cause
1. **Review model** had a `reviewable()` method that returned `null` instead of the proper `morphTo()` relationship
2. Multiple models (Buyer, Transport, Product, Expert) still had active `morphMany(Review::class, 'reviewable')` relationships
3. When Laravel tried to serialize these models during authentication, the broken morphTo chain caused infinite loops

## Solution

### 1. Fixed Review Model
- Changed `reviewable()` method from returning `null` to properly returning `$this->morphTo()`
- This allows polymorphic relationships to work correctly

```php
public function reviewable()
{
    return $this->morphTo();
}
```

### 2. Disabled Relationships in Auth-Critical Models
Disabled all relationships in models that could be loaded during authentication to prevent any recursion:

#### Buyer Model
- Disabled: `user()`, `orders()`, `reviews()`, `wishlists()`, `cart()`
- Updated accessor methods to return defaults

#### Transport Model  
- Disabled: `user()`, `deliveries()`, `reviews()`

#### Product Model
- Disabled: `farmer()`, `crop()`, `category()`, `orderItems()`, `reviews()`, `cartItems()`, `wishlists()`, `productImages()`
- Updated accessor methods to return defaults

#### Expert Model
- Disabled: `user()`, `consultations()`, `reviews()`
- Updated accessor methods to return defaults

### 3. Pattern Applied
All disabled relationships now return `null`:

```php
public function user() { return null; }
public function orders() { return null; }
// etc...
```

This prevents any attempt to load relationships during authentication, which is the critical path where the recursion occurred.

## Why This Works
- Login uses raw database queries, not models
- Auth guards fetch users without loading relationships
- Models that aren't loaded during auth don't cause recursion
- Review model now has a proper polymorphic relationship for use elsewhere

## Testing
Test login with:
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

Should now return user data and token without stack overflow error.

## Future Considerations
- When you need relationship data outside of auth flow, consider using direct DB queries
- Consider using repository pattern to load relationships only when needed
- Monitor for any similar recursion patterns in other model relationships

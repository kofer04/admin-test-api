# Backend Test Suite

Comprehensive test suite for the Admin Test API, covering authentication, reports, resources, and RBAC (Role-Based Access Control).

## Overview

This test suite follows **BDD (Behavior-Driven Development)** principles using the **Given/When/Then** pattern and provides comprehensive coverage for:

- **Cookie-Based Authentication** (Laravel Sanctum + Fortify)
- **Reports** (Job Bookings, Conversion Funnel)
- **Resources** (Markets, Users, Settings)
- **RBAC** (Role and Permission-based authorization)

## Test Structure

```
tests/
├── Feature/
│   ├── Auth/
│   │   └── CookieAuthenticationTest.php      # Authentication tests
│   ├── Reports/
│   │   ├── JobBookingsReportTest.php         # Job Bookings report tests
│   │   └── ConversionFunnelReportTest.php    # Conversion Funnel report tests
│   └── Resources/
│       ├── MarketResourceTest.php            # Market resource tests
│       ├── UserResourceTest.php              # User resource tests
│       └── SettingResourceTest.php           # Setting resource tests
├── Traits/
│   ├── AuthenticationHelpers.php             # Auth helper methods
│   ├── MarketHelpers.php                     # Market helper methods
│   └── GivenWhenThen.php                     # BDD scenario methods
└── TestCase.php                              # Base test case
```

## Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# Authentication tests
php artisan test --group=auth

# Report tests
php artisan test --group=reports

# Resource tests
php artisan test --group=resources

# RBAC tests
php artisan test --group=rbac
```

### Run Specific Test Files
```bash
php artisan test tests/Feature/Auth/CookieAuthenticationTest.php
php artisan test tests/Feature/Reports/JobBookingsReportTest.php
php artisan test tests/Feature/Reports/ConversionFunnelReportTest.php
php artisan test tests/Feature/Resources/MarketResourceTest.php
php artisan test tests/Feature/Resources/UserResourceTest.php
php artisan test tests/Feature/Resources/SettingResourceTest.php
```

### Run Tests with Specific Tags
```bash
# Cookie authentication tests
php artisan test --group=cookie-auth

# Export functionality tests
php artisan test --group=export

# Pagination tests
php artisan test --group=pagination

# Market access control tests
php artisan test --group=market-access
```

### Run with Coverage (if configured)
```bash
php artisan test --coverage
```

## Test Categories

### 1. Authentication Tests (`Auth/CookieAuthenticationTest.php`)

Tests cookie-based authentication using Laravel Sanctum and Fortify:

- ✅ Login with valid credentials
- ✅ Login failure with invalid credentials
- ✅ CSRF token protection
- ✅ Session persistence
- ✅ Logout functionality
- ✅ Rate limiting for brute-force protection
- ✅ Authenticated user data with roles

**Key Scenarios:**
```php
// Given: A user exists in the database
// When: User attempts to login with valid credentials
// Then: User should be authenticated
```

### 2. Report Tests

#### Job Bookings Report (`Reports/JobBookingsReportTest.php`)

- ✅ Super Admin access
- ✅ Market User with permission access
- ✅ Market User without permission denied
- ✅ Unauthenticated user denied
- ✅ CSV export with proper permissions
- ✅ Date range filtering
- ✅ Market filtering
- ✅ Market-based data isolation

#### Conversion Funnel Report (`Reports/ConversionFunnelReportTest.php`)

- ✅ Super Admin access
- ✅ Market User with permission access
- ✅ Market User without permission denied
- ✅ Unauthenticated user denied
- ✅ CSV export with proper permissions
- ✅ Date range filtering
- ✅ Market filtering
- ✅ Validation of invalid date formats
- ✅ Cross-role data isolation

**Key Scenarios:**
```php
// Given: A Market User with read permission exists
// And: User has access to specific markets
// When: Market User requests the report
// Then: Report data should be returned with accessible markets only
```

### 3. Resource Tests (Minimal - Core Functionality)

#### Markets (`Resources/MarketResourceTest.php`)

- ✅ Super Admin can view all markets
- ✅ Market User can view accessible markets only
- ✅ Permission-based access control
- ✅ Unauthenticated access denied
- ✅ CSV export functionality
- ✅ Export permission-based access control

#### Users (`Resources/UserResourceTest.php`)

- ✅ Super Admin can view all users
- ✅ Permission-based access control
- ✅ Unauthenticated access denied
- ✅ CSV export functionality
- ✅ Export permission-based access control
- ✅ Self-profile access

#### Settings (`Resources/SettingResourceTest.php`)

- ✅ Authenticated user can view their settings
- ✅ Super Admin can view settings
- ✅ Unauthenticated access denied
- ✅ Update settings
- ✅ Multiple data type support (string, integer, boolean, json)
- ✅ Validation

**Key Scenarios:**
```php
// Given: Two users with different market access exist
// When: Each user requests their accessible markets
// Then: Each user should only see their own markets
```

## Test Helpers

### AuthenticationHelpers Trait

Provides helper methods for authentication scenarios:

```php
// Create users with specific roles
$admin = $this->createSuperAdmin();
$marketUser = $this->createMarketUser([], [Permission::MarketsRead->value]);
$guestUser = $this->createGuestUser();

// Authenticate users
$this->authenticateUser($user);
$this->loginWithCredentials('email@test.com', 'password');

// Assertions
$this->assertAuthenticated();
$this->assertGuest();
```

### MarketHelpers Trait

Provides helper methods for market management:

```php
// Create markets
$market = $this->createMarket(['name' => 'Test Market']);
$markets = $this->createMarkets(5);

// Assign markets to users
$this->assignMarketsToUser($user, [$market1->id, $market2->id]);
$market = $this->createMarketForUser($user);
```

### GivenWhenThen Trait

Provides BDD-style test structure:

```php
// Given: Setup initial state
$user = $this->given('a user exists', function () {
    return User::factory()->create();
});

// When: Perform action
$response = $this->when('user makes a request', function () {
    return $this->getJson('/api/endpoint');
});

// Then: Assert outcome
$this->then('response should be successful', function () use ($response) {
    $response->assertOk();
});

// And: Chain additional steps
$this->and('additional condition', function () {
    // Additional setup or assertions
});
```

## RBAC Testing

The test suite comprehensively tests Role-Based Access Control:

### Roles

- **Super Admin**: Full access to all resources and reports
- **Market User**: Limited access based on assigned permissions and markets

### Permissions

Reports:
- `read-report:job-bookings`
- `export-report:job-bookings`
- `read-report:conversion-funnel`
- `export-report:conversion-funnel`

Resources:
- `markets:read`
- `markets:write`
- `markets:export`
- `users:read`
- `users:write`
- `users:export`

### RBAC Test Patterns

Each endpoint is tested with:
1. ✅ Super Admin access (should succeed)
2. ✅ Authorized user access (with proper permission, should succeed)
3. ✅ Unauthorized user access (without permission, should be denied)
4. ✅ Unauthenticated access (should be denied)

## Best Practices

1. **Use Given/When/Then**: All tests follow BDD principles for clarity
2. **Test Isolation**: Each test is independent and uses `RefreshDatabase`
3. **Helper Traits**: Reusable helper methods for common operations
4. **Descriptive Names**: Test method names clearly describe the scenario
5. **Comprehensive Coverage**: Tests cover success, failure, and edge cases
6. **RBAC Focused**: Every endpoint tested with different user roles
7. **Market Isolation**: Ensures users only access their authorized data

## Database Seeding for Tests

Tests use factories for data creation:

```php
// User factory
User::factory()->create(['email' => 'test@example.com']);

// Market factory
Market::factory()->create(['name' => 'Test Market']);
```

## Writing New Tests

When adding new tests:

1. Use appropriate test group tags (`@group`)
2. Follow Given/When/Then pattern
3. Test all RBAC scenarios
4. Include positive and negative test cases
5. Use helper traits for common operations
6. Add descriptive comments

Example:

```php
/**
 * @test
 * @group resources
 * @group new-feature
 * @group rbac
 */
public function super_admin_can_access_new_feature(): void
{
    // Given: A Super Admin user
    $admin = $this->given('a Super Admin user exists', function () {
        return $this->createSuperAdmin();
    });
    
    $this->and('user is authenticated', function () use ($admin) {
        $this->actingAs($admin, 'web');
    });
    
    // When: Admin accesses the new feature
    $response = $this->when('admin accesses new feature', function () {
        return $this->getJson('/api/v1/new-feature');
    });
    
    // Then: Access should be granted
    $this->then('access should be granted', function () use ($response) {
        $response->assertOk();
    });
}
```

## Continuous Integration

This test suite is designed to run in CI/CD pipelines. Ensure:

1. Database is properly configured for testing environment
2. All migrations are run before tests
3. Test database is isolated from development/production
4. Environment variables are properly set in `phpunit.xml`

## Troubleshooting

### Common Issues

**Issue**: Tests fail with "CSRF token mismatch"
**Solution**: Ensure tests use `$this->get('/sanctum/csrf-cookie')` before login

**Issue**: Tests fail with "Unauthenticated"
**Solution**: Use `$this->actingAs($user, 'web')` to authenticate users

**Issue**: Market isolation not working
**Solution**: Ensure markets are properly assigned using `assignMarketsToUser()`

**Issue**: Permission denied errors
**Solution**: Verify roles and permissions are properly seeded/assigned

## Test Metrics

- **Total Test Files**: 6
- **Test Categories**: Authentication (11 tests), Reports (24 tests), Resources (20 tests)
- **Total Tests**: ~55 test scenarios
- **Coverage Areas**: 
  - Auth: Login, Logout, CSRF, Sessions, Rate Limiting
  - Reports: Full filtering, date ranges, market filters, permissions, exports
  - Resources: Core CRUD, RBAC, Exports
- **RBAC Scenarios**: Every endpoint tested with different user roles
- **Given/When/Then**: All tests follow BDD pattern

## Contributing

When contributing new tests:

1. Follow existing patterns and conventions
2. Use helper traits where applicable
3. Add appropriate test groups
4. Document complex test scenarios
5. Ensure tests are isolated and idempotent
6. Test both success and failure cases
7. Include RBAC variations for all endpoints

---

**Note**: This test suite uses Laravel's built-in testing framework with PHPUnit. Make sure to run `composer install` to ensure all dependencies are available.


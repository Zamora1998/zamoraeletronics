# QueryBuilder Documentation

A fluent, SQL-first query builder for PHP/MySQL that maintains single responsibility by separating query construction from execution. Features comprehensive SQL injection protection through automatic parameterization of all literals in queries, expressions, and conditional statements.

## Table of Contents

- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Select Queries](#select-queries)
- [Where Clauses](#where-clauses)
- [Joins](#joins)
- [Ordering and Grouping](#ordering-and-grouping)
- [Conditional Statements (IF and CASE)](#conditional-statements-if-and-case)
- [Insert, Update, Delete](#insert-update-delete)
- [Advanced Features](#advanced-features)
- [Best Practices](#best-practices)
- [Method Reference](#method-reference)

## Installation

```php
    function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
        require_once __ROOT__ . '/assets/php/libQueryBuilder.php';
    }
```

## Basic Usage

The QueryBuilder follows a fluent interface pattern. Build your query, then execute it with your database connection:

```php
// Build the query
$query = $qb->table('users')
    ->select(['id', 'name', 'email'])
    ->where('status', '=', 'active')
    ->build();

// Execute the query
$result = $this->objDbConn->prepProcessQuery(
    $query['sql'],
    $query['types'],
    $query['params']
);

// Access the data
if ($result['result']) {
    foreach ($result['data'] as $user) {
        echo $user['name'];
    }
}
```

## Select Queries

### Basic Select

```php
// SELECT * FROM users
$query = $qb->table('users')->build();

// SELECT id, name FROM users
$query = $qb->table('users')
    ->select(['id', 'name'])
    ->build();

// Single column
$query = $qb->table('users')
    ->select('email')
    ->build();
```

### Expressions in SELECT

All literals in SELECT expressions are automatically parameterized:

```php
// Arithmetic expressions
$query = $qb->table('products')
    ->select(['name', 'price * 1.2 AS final_price'])
    ->build();
// SQL: SELECT name, price * ? AS final_price
// Params: [1.2]

// String concatenation
$query = $qb->table('users')
    ->select(['CONCAT(first_name, " ", last_name) AS full_name'])
    ->build();
// SQL: SELECT CONCAT(first_name, ?, last_name) AS full_name
// Params: [' ']

// Complex expressions
$query = $qb->table('orders')
    ->select(['order_id', 'total * 0.9 - 5 AS discounted_total'])
    ->build();
// SQL: SELECT order_id, total * ? - ? AS discounted_total
// Params: [0.9, 5]
```

### DISTINCT

```php
// SELECT DISTINCT city FROM customers
$query = $qb->table('customers')
    ->select('city')
    ->distinct()
    ->build();
```

### Aggregate Functions

```php
// Use raw SQL in select (literals are parameterized)
$query = $qb->table('orders')
    ->select(['COUNT(*) as total', 'SUM(amount) as sum'])
    ->build();
```

## Where Clauses

### Basic Where

The `where()` method accepts an optional fourth `$boolean` parameter (`'AND'` by default) to control how the condition is joined to the previous one. Pass `'OR'` for OR conditions.

```php
// WHERE status = 'active'
$query = $qb->table('users')
    ->where('status', '=', 'active')
    ->build();

// Multiple conditions (AND)
$query = $qb->table('users')
    ->where('status', '=', 'active')
    ->where('age', '>', 18)
    ->build();

// OR conditions
$query = $qb->table('users')
    ->where('role', '=', 'admin')
    ->where('role', '=', 'moderator', 'OR')
    ->build();
```

### Nested WHERE Groups

Pass a callable to `where()` to create grouped conditions wrapped in parentheses. The optional second parameter sets the boolean connector (`'AND'` or `'OR'`) for the group itself.

```php
// WHERE status = 'active' AND (role = 'admin' OR role = 'moderator')
$query = $qb->table('users')
    ->where('status', '=', 'active')
    ->where(function($q) {
        $q->where('role', '=', 'admin')
          ->where('role', '=', 'moderator', 'OR');
    })
    ->build();

// OR group: WHERE featured = 1 OR (role = 'admin' AND status = 'active')
$query = $qb->table('users')
    ->where('featured', '=', 1)
    ->where(function($q) {
        $q->where('role', '=', 'admin')
          ->where('status', '=', 'active');
    }, 'OR')
    ->build();
```

### WHERE IN

```php
// WHERE id IN (1, 2, 3)
$query = $qb->table('users')
    ->whereIn('id', [1, 2, 3])
    ->build();

// WHERE status IN ('active', 'pending')
$query = $qb->table('users')
    ->whereIn('status', ['active', 'pending'])
    ->build();

// OR WHERE IN
$query = $qb->table('users')
    ->where('status', '=', 'active')
    ->whereIn('role', ['admin', 'moderator'], 'OR')
    ->build();
```

### WHERE BETWEEN

```php
// WHERE age BETWEEN 18 AND 65
$query = $qb->table('users')
    ->whereBetween('age', 18, 65)
    ->build();

// WHERE created_at BETWEEN dates
$query = $qb->table('orders')
    ->whereBetween('created_at', '2024-01-01', '2024-12-31')
    ->build();

// OR WHERE BETWEEN
$query = $qb->table('products')
    ->where('category', '=', 'electronics')
    ->whereBetween('price', 100, 500, 'OR')
    ->build();

// WHERE NOT BETWEEN
$query = $qb->table('products')
    ->whereNotBetween('price', 50, 100)
    ->build();
```

### WHERE NULL

```php
// WHERE email IS NULL
$query = $qb->table('users')
    ->whereNull('email')
    ->build();

// WHERE email IS NOT NULL
$query = $qb->table('users')
    ->whereNotNull('email')
    ->build();

// OR WHERE NULL
$query = $qb->table('users')
    ->whereNull('phone')
    ->whereNull('email', 'OR')
    ->build();
```

### WHERE LIKE

```php
// WHERE name LIKE 'John%'
$query = $qb->table('users')
    ->whereLike('name', 'John%')
    ->build();

// Search with wildcards
$search = 'smith';
$query = $qb->table('users')
    ->whereLike('name', "%{$search}%")
    ->build();

// WHERE NOT LIKE
$query = $qb->table('products')
    ->whereLike('name', '%phone%')
    ->whereNotLike('name', '%case%')
    ->build();

// OR WHERE LIKE
$query = $qb->table('users')
    ->whereLike('email', '%@gmail.com')
    ->whereLike('email', '%@yahoo.com', 'OR')
    ->build();
```

### WHERE Column Comparison

```php
// WHERE created_at = updated_at
$query = $qb->table('users')
    ->whereColumn('created_at', '=', 'updated_at')
    ->build();

// WHERE first_name = last_name
$query = $qb->table('users')
    ->whereColumn('first_name', '=', 'last_name')
    ->build();
```

### WHERE EXISTS

```php
// Find users who have posts
$query = $qb->table('users')
    ->whereExists(function($q) {
        $q->table('posts')
            ->select('1')
            ->whereColumn('posts.user_id', '=', 'users.id');
    })
    ->build();

// WHERE NOT EXISTS
$query = $qb->table('users')
    ->whereNotExists(function($q) {
        $q->table('orders')
            ->select('1')
            ->whereColumn('orders.user_id', '=', 'users.id');
    })
    ->build();

// OR WHERE EXISTS
$query = $qb->table('products')
    ->where('featured', '=', 1)
    ->whereExists(function($q) {
        $q->table('sales')
            ->select('1')
            ->whereColumn('sales.product_id', '=', 'products.id')
            ->where('sales.created_at', '>', date('Y-m-d', strtotime('-7 days')));
    }, 'OR')
    ->build();
```

### WHERE RAW

Use `whereRaw()` to compare a column against a raw, unparameterized SQL expression. Use with caution — raw expressions are not sanitized.

```php
// WHERE total > (SELECT AVG(total) FROM orders)
$query = $qb->table('orders')
    ->whereRaw('total', '>', '(SELECT AVG(total) FROM orders)')
    ->build();

// WHERE updated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
$query = $qb->table('users')
    ->whereRaw('updated_at', '>', 'DATE_SUB(NOW(), INTERVAL 7 DAY)')
    ->build();
```

## Joins

### Basic Joins

```php
// INNER JOIN
$query = $qb->table('orders')
    ->select(['orders.*', 'customers.name'])
    ->join('customers', 'orders.customer_id', '=', 'customers.id')
    ->build();

// LEFT JOIN
$query = $qb->table('users')
    ->select(['users.*', 'profiles.bio'])
    ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
    ->build();

// RIGHT JOIN
$query = $qb->table('orders')
    ->select(['orders.*', 'customers.name'])
    ->rightJoin('customers', 'orders.customer_id', '=', 'customers.id')
    ->build();

// Multiple joins
$query = $qb->table('orders')
    ->select(['orders.*', 'customers.name', 'products.title'])
    ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
    ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
    ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
    ->build();
```

### Multi-Condition Joins

Use `joinOn()` immediately after `join()` or `leftJoin()` to add additional ON conditions to the most recently added join:

```php
// JOIN with multiple ON conditions
$query = $qb->table('orders')
    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
    ->joinOn('orders.status', '=', 'order_items.status')
    ->build();

// LEFT JOIN with an AND and an OR condition
$query = $qb->table('users')
    ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
    ->joinOn('roles.active', '=', 'users.active', 'AND')
    ->joinOn('roles.legacy_id', '=', 'users.legacy_role_id', 'OR')
    ->build();
```

### Join with Subquery

```php
// Join with a subquery
$query = $qb->table('users')
    ->joinSubQuery(
        function($q) {
            $q->table('orders')
                ->select(['user_id', 'COUNT(*) as order_count', 'SUM(total) as total_spent'])
                ->groupBy('user_id');
        },
        'user_stats',
        'users.id',
        '=',
        'user_stats.user_id'
    )
    ->select(['users.*', 'user_stats.order_count', 'user_stats.total_spent'])
    ->build();

// Left join with subquery
$query = $qb->table('products')
    ->leftJoinSubQuery(
        function($q) {
            $q->table('order_items')
                ->select(['product_id', 'SUM(quantity) as total_sold'])
                ->groupBy('product_id');
        },
        'sales',
        'products.id',
        '=',
        'sales.product_id'
    )
    ->build();
```

## Ordering and Grouping

### ORDER BY

```php
// Single column ascending
$query = $qb->table('users')
    ->orderBy('name', 'ASC')
    ->build();

// Multiple order columns
$query = $qb->table('users')
    ->orderBy('status', 'DESC')
    ->orderBy('created_at', 'DESC')
    ->build();
```

### GROUP BY

```php
// Simple group by
$query = $qb->table('orders')
    ->select(['customer_id', 'COUNT(*) as order_count'])
    ->groupBy('customer_id')
    ->build();

// Multiple columns
$query = $qb->table('sales')
    ->select(['region', 'product_id', 'SUM(amount) as total'])
    ->groupBy(['region', 'product_id'])
    ->build();
```

### HAVING

```php
// Group with HAVING
$query = $qb->table('orders')
    ->select(['customer_id', 'COUNT(*) as order_count'])
    ->groupBy('customer_id')
    ->having('order_count > 5')
    ->build();
```

### LIMIT and OFFSET

```php
// First 10 records
$query = $qb->table('users')
    ->limit(10)
    ->build();

// Pagination (skip 20, take 10)
$query = $qb->table('users')
    ->limit(10)
    ->offset(20)
    ->build();
```

## Conditional Statements (IF and CASE)

The QueryBuilder provides powerful IF and CASE statement support with automatic parameterization of all literals.

### IF Statements

IF statements allow for simple binary conditional logic:

```php
// Basic IF
$statusLabel = $qb->if(
    'status = 1',
    'Active',
    'Inactive'
);

$query = $qb->table('users')
    ->select(['id', 'name', 'label' => $statusLabel])
    ->build();
// SQL: SELECT id, name, IF(status = ?, ?, ?) AS label
// Params: [1, 'Active', 'Inactive']

// Nested IF statements
$daysCalculation = $qb->if(
    'intervaltype = "month"',
    'interval * 30',
    $qb->if(
        'intervaltype = "week"',
        'interval * 7',
        'interval'
    )
);

$query = $qb->table('payment_conditions')
    ->select(['days' => $daysCalculation])
    ->build();
// SQL: SELECT IF(intervaltype = ?, interval * ?,
//              IF(intervaltype = ?, interval * ?, interval)) AS days
// Params: ['month', 30, 'week', 7]

// IF with expressions
$dealerCode = $qb->if(
    'd.name = "EDMI"',
    '"ARA"',
    $qb->if(
        'd.id = 2',
        "CONCAT(d.id, 'CATCU')",
        '"Other"'
    )
);

$query = $qb->table('dealers as d')
    ->select(['dealer_code' => $dealerCode])
    ->build();
// SQL: SELECT IF(d.name = ?, ?, IF(d.id = ?, CONCAT(d.id, ?), ?)) AS dealer_code
// Params: ['EDMI', 'ARA', 2, 'CATCU', 'Other']

// IF in WHERE clause
$categoryType = $qb->if('price > 1000', 'Premium', 'Standard');
$query = $qb->table('products')
    ->where('category', '=', $categoryType)
    ->build();
```

### CASE Statements

CASE statements are ideal for multiple conditional branches:

```php
// Simple CASE (searched CASE)
$statusLabel = $qb->case()
    ->when('status = 1', 'Active')
    ->when('status = 2', 'Pending')
    ->when('status = 3', 'Suspended')
    ->else('Inactive');

$query = $qb->table('users')
    ->select(['id', 'name', 'status_label' => $statusLabel])
    ->build();
// SQL: SELECT id, name, CASE
//      WHEN status = ? THEN ?
//      WHEN status = ? THEN ?
//      WHEN status = ? THEN ?
//      ELSE ? END AS status_label
// Params: [1, 'Active', 2, 'Pending', 3, 'Suspended', 'Inactive']

// Simple CASE with column (CASE column WHEN...)
$gradeLabel = $qb->case('score')
    ->when('90', 'A')
    ->when('80', 'B')
    ->when('70', 'C')
    ->else('F');

$query = $qb->table('students')
    ->select(['name', 'grade' => $gradeLabel])
    ->build();

// CASE with expressions
$shippingCost = $qb->case()
    ->when('country = "US" AND total > 100', 0)
    ->when('country = "US"', 15)
    ->when('country = "CA" AND total > 150', 0)
    ->when('country = "CA"', 25)
    ->else(50);

$query = $qb->table('orders')
    ->select(['order_id', 'shipping' => $shippingCost])
    ->build();

// CASE with column expressions
$discountedPrice = $qb->case()
    ->when('customer_type = "wholesale"', 'price * 0.7')
    ->when('quantity >= 10', 'price * 0.9')
    ->else('price');

$query = $qb->table('products')
    ->select(['product_name', 'final_price' => $discountedPrice])
    ->build();
// SQL: SELECT product_name, CASE
//      WHEN customer_type = ? THEN price * ?
//      WHEN quantity >= ? THEN price * ?
//      ELSE price END AS final_price
// Params: ['wholesale', 0.7, 10, 0.9]
```

### Nested CASE Statements

```php
// Customer tier with nested conditions
$customerTier = $qb->case()
    ->when('total_spent > 10000',
        $qb->case()
            ->when('account_age_years >= 5', 'Platinum Plus')
            ->when('account_age_years >= 3', 'Platinum')
            ->else('Gold Plus')
    )
    ->when('total_spent > 5000',
        $qb->case()
            ->when('account_age_years >= 5', 'Gold')
            ->else('Silver Plus')
    )
    ->when('total_spent > 1000', 'Silver')
    ->else('Bronze');

$query = $qb->table('customers')
    ->select(['name', 'tier' => $customerTier])
    ->build();
```

### Mixing IF and CASE

```php
$finalStatus = $qb->case()
    ->when('cancelled = 1', 'Cancelled')
    ->when('completed = 1',
        $qb->if('rating >= 4', 'Excellent', 'Completed')
    )
    ->else('In Progress');

$query = $qb->table('orders')
    ->select(['order_id', 'status' => $finalStatus])
    ->build();
```

### Important Notes on IF/CASE

- All string and number literals are automatically parameterized for security.
- Column names and expressions (like `price * 0.7`) are preserved as-is.
- Strings within expressions (like `CONCAT(name, 'suffix')`) are parameterized.
- IF and CASE can be nested to any depth.
- Both can be used in SELECT and WHERE clauses.
- CASE without ELSE returns NULL if no conditions match.

## Insert, Update, Delete

### INSERT

```php
$query = $qb->table('users')
    ->insert([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => 'active',
        'age' => 25
    ]);

$result = $this->objDbConn->prepProcessQuery(
    $query['sql'],
    $query['types'],
    $query['params']
);
```

### UPDATE

```php
// Update with WHERE
$query = $qb->table('users')
    ->where('id', '=', 1)
    ->update([
        'status' => 'inactive',
        'updated_at' => date('Y-m-d H:i:s')
    ]);

$result = $this->objDbConn->prepProcessQuery(
    $query['sql'],
    $query['types'],
    $query['params']
);

// Update multiple records
$query = $qb->table('products')
    ->where('category', '=', 'electronics')
    ->where('stock', '<', 10)
    ->update(['on_sale' => 1]);
```

### DELETE

```php
// Delete with WHERE
$query = $qb->table('users')
    ->where('status', '=', 'deleted')
    ->delete();

$result = $this->objDbConn->prepProcessQuery(
    $query['sql'],
    $query['types'],
    $query['params']
);

// Delete with multiple conditions
$query = $qb->table('logs')
    ->where('created_at', '<', date('Y-m-d', strtotime('-90 days')))
    ->delete();
```

## Advanced Features

### Common Table Expressions (CTEs)

```php
// Basic CTE
$query = $qb->with('high_value_customers', function($q) {
        $q->table('customers')
            ->select(['id', 'name', 'total_spent'])
            ->where('total_spent', '>', 10000);
    })
    ->table('high_value_customers')
    ->select(['high_value_customers.*', 'orders.order_date'])
    ->leftJoin('orders', 'high_value_customers.id', '=', 'orders.customer_id')
    ->build();

// Multiple CTEs
$query = $qb->with('active_users', function($q) {
        $q->table('users')
            ->select('id')
            ->where('status', '=', 'active');
    })
    ->with('recent_orders', function($q) {
        $q->table('orders')
            ->select(['user_id', 'COUNT(*) as order_count'])
            ->where('created_at', '>', date('Y-m-d', strtotime('-30 days')))
            ->groupBy('user_id');
    })
    ->table('active_users')
    ->leftJoin('recent_orders', 'active_users.id', '=', 'recent_orders.user_id')
    ->build();

// Recursive CTE
$query = $qb->withRecursive('employee_hierarchy', function($q) {
        $q->table('employees')
            ->select(['id', 'name', 'manager_id', '1 as level'])
            ->whereNull('manager_id');
    })
    ->table('employee_hierarchy')
    ->build();
```

### Subqueries

#### FROM Subquery

Use `fromSubQuery()` to use a subquery as the FROM clause instead of a table name:

```php
// SELECT * FROM (SELECT ...) AS alias
$query = $qb->fromSubQuery(function($q) {
        $q->table('orders')
            ->select(['user_id', 'SUM(total) as lifetime_value'])
            ->groupBy('user_id');
    }, 'order_totals')
    ->select(['user_id', 'lifetime_value'])
    ->where('lifetime_value', '>', 1000)
    ->build();

// Can also accept a pre-built QueryBuilder instance
$inner = $qb->subQuery(function($q) {
    $q->table('products')->select(['id', 'name'])->where('active', '=', 1);
});

$query = $qb->reset()
    ->fromSubQuery($inner, 'active_products')
    ->build();
```

#### WHERE IN Subquery

```php
// Find users who have completed orders
$query = $qb->table('users')
    ->whereInSubQuery('id', function($q) {
        $q->table('orders')
            ->select('user_id')
            ->where('status', '=', 'completed')
            ->distinct();
    })
    ->build();

// WHERE NOT IN subquery
$query = $qb->table('products')
    ->whereNotInSubQuery('id', function($q) {
        $q->table('order_items')
            ->select('product_id')
            ->where('created_at', '>', date('Y-m-d', strtotime('-90 days')));
    })
    ->build();
```

#### Reusable Subqueries

```php
// Create a reusable subquery
$activeUsersQuery = $qb->subQuery(function($q) {
    $q->table('users')
        ->select('id')
        ->where('status', '=', 'active')
        ->where('email_verified', '=', 1);
});

// Use it multiple times
$orders = $qb->table('orders')
    ->whereInSubQuery('user_id', $activeUsersQuery)
    ->build();

$comments = $qb->table('comments')
    ->whereInSubQuery('user_id', $activeUsersQuery)
    ->build();
```

### UNION Queries

```php
// Basic UNION
$query = $qb->table('users')
    ->select(['id', 'name', 'email'])
    ->where('status', '=', 'active')
    ->union(function($q) {
        $q->table('users')
            ->select(['id', 'name', 'email'])
            ->where('status', '=', 'pending');
    })
    ->build();

// UNION ALL (keeps duplicates)
$query = $qb->table('orders_2023')
    ->select(['order_id', 'customer_id', 'total'])
    ->unionAll(function($q) {
        $q->table('orders_2024')
            ->select(['order_id', 'customer_id', 'total']);
    })
    ->orderBy('order_id', 'DESC')
    ->build();

// Multiple UNIONs
$query = $qb->table('customers')
    ->select(['id', 'name', "'customer' as type"])
    ->union(function($q) {
        $q->table('suppliers')
            ->select(['id', 'name', "'supplier' as type"]);
    })
    ->union(function($q) {
        $q->table('partners')
            ->select(['id', 'name', "'partner' as type"]);
    })
    ->orderBy('name', 'ASC')
    ->build();
```

### Query Reset and Reuse

```php
$qb = new QueryBuilder();

// First query
$query1 = $qb->table('users')
    ->where('status', '=', 'active')
    ->build();

// Reset and build new query
$query2 = $qb->reset()
    ->table('orders')
    ->where('status', '=', 'pending')
    ->build();
```

## Best Practices

### 1. Always Use Prepared Statements

The QueryBuilder automatically handles parameter binding. Never concatenate user input:

```php
// GOOD - Parameters are safely bound
$query = $qb->table('users')
    ->where('email', '=', $userInput)
    ->build();

// BAD - Don't do this
$sql = "SELECT * FROM users WHERE email = '$userInput'";
```

### 2. Automatic Parameterization

The QueryBuilder automatically parameterizes all literals in WHERE conditions, SELECT expressions, and IF/CASE statements:

```php
$query = $qb->table('products')
    ->select(['name', 'price * 1.2 AS final_price'])
    ->where('category', '=', 'electronics')
    ->build();
// SQL: SELECT name, price * ? AS final_price WHERE category = ?
// Params: [1.2, 'electronics']
```

### 3. Use whereRaw Carefully

`whereRaw()` bypasses parameterization. Only use it with trusted, static SQL expressions — never with user-supplied input:

```php
// SAFE - static SQL expression
$query = $qb->table('orders')
    ->whereRaw('updated_at', '>', 'DATE_SUB(NOW(), INTERVAL 7 DAY)')
    ->build();

// DANGEROUS - never pass user input to whereRaw
// $query = $qb->table('users')->whereRaw('name', '=', $userInput); // DON'T DO THIS
```

### 4. Escape LIKE Wildcards

When using user input in LIKE queries, escape special characters if you don't want them to act as wildcards:

```php
function escapeLike($value) {
    return str_replace(['%', '_'], ['\\%', '\\_'], $value);
}

$search = escapeLike($userInput);
$query = $qb->table('users')
    ->whereLike('name', "%{$search}%")
    ->build();
```

### 5. Use Meaningful Column Selection

Select only the columns you need:

```php
// GOOD - Select specific columns
$query = $qb->table('users')
    ->select(['id', 'name', 'email'])
    ->build();

// AVOID - Don't select * in production
$query = $qb->table('users')->build();
```

### 6. Handle Errors Properly

Always check the result from your database execution:

```php
$query = $qb->table('users')
    ->where('id', '=', $userId)
    ->build();

$result = $this->objDbConn->prepProcessQuery(
    $query['sql'],
    $query['types'],
    $query['params']
);

if ($result['result']) {
    $data = $result['data'];
} else {
    error_log($result['error']);
    // Handle error appropriately
}
```

### 7. Use Transactions for Multiple Operations

When performing multiple related operations:

```php
$db->mysqli->begin_transaction();

try {
    $orderQuery = $qb->table('orders')->insert([
        'customer_id' => $customerId,
        'total' => $total
    ]);
    $result[] = $this->objDbConn->prepProcessQuery($orderQuery['sql'], $orderQuery['types'], $orderQuery['params']);

    $itemQuery = $qb->reset()->table('order_items')->insert([
        'order_id' => $orderId,
        'product_id' => $productId,
        'quantity' => $quantity
    ]);
    $result[] = $this->objDbConn->prepProcessQuery($itemQuery['sql'], $itemQuery['types'], $itemQuery['params']);

    $db->mysqli->commit();
} catch (Exception $e) {
    $db->mysqli->rollback();
    error_log($e->getMessage());
}
```

### 8. Use CASE for Multiple Conditions

When queries have multiple conditional branches, use CASE instead of nested IFs:

```php
// Cleaner and more readable
$status = $qb->case()
    ->when('status = 1', 'Active')
    ->when('status = 2', 'Pending')
    ->when('status = 3', 'Suspended')
    ->else('Inactive');

// Instead of deeply nested IFs
$status = $qb->if('status = 1', 'Active',
    $qb->if('status = 2', 'Pending',
        $qb->if('status = 3', 'Suspended', 'Inactive')
    )
);
```

### 9. Use CTEs for Complex Queries

When queries get complex, CTEs improve readability:

```php
$query = $qb->with('monthly_sales', function($q) {
        $q->table('orders')
            ->select(['MONTH(created_at) as month', 'SUM(total) as revenue'])
            ->where('status', '=', 'completed')
            ->groupBy('month');
    })
    ->table('monthly_sales')
    ->where('revenue', '>', 10000)
    ->build();
```

### 10. Create Conditional Statements Before SELECT

For better readability, build complex IF/CASE statements before chaining into `select()`:

```php
// GOOD - Clear and readable
$tierCalculation = $qb->case()
    ->when('total_spent > 10000', 'Platinum')
    ->when('total_spent > 5000', 'Gold')
    ->when('total_spent > 1000', 'Silver')
    ->else('Bronze');

$query = $qb->table('customers')
    ->select(['name', 'tier' => $tierCalculation])
    ->build();
```

### 11. Index Your Queries

Remember to create appropriate database indexes for columns used in WHERE, JOIN, and ORDER BY clauses.

## Method Reference

### Select Methods

- `table(string $table)` — Set the FROM table
- `select(string|array|IfStatement|CaseStatement $columns)` — Set columns to select
- `distinct()` — Add DISTINCT modifier
- `if(string $condition, mixed $trueValue, mixed $falseValue): IfStatement` — Create an IF statement
- `case(?string $column = null): CaseStatement` — Create a CASE statement

### Where Methods

- `where(string|callable $column, ?string $operator, mixed $value, string $boolean = 'AND')` — Basic WHERE or nested group (pass callable)
- `whereRaw(string $column, string $operator, string $rawExpression, string $boolean = 'AND')` — WHERE with raw, unparameterized expression
- `whereExists(callable $callback, string $boolean = 'AND')` — WHERE EXISTS
- `whereNotExists(callable $callback, string $boolean = 'AND')` — WHERE NOT EXISTS
- `whereColumn(string $first, string $operator, string $second, string $boolean = 'AND')` — Compare two columns
- `whereIn(string $column, array $values, string $boolean = 'AND')` — WHERE IN
- `whereNotIn(string $column, array $values, string $boolean = 'AND')` — WHERE NOT IN
- `whereBetween(string $column, mixed $min, mixed $max, string $boolean = 'AND')` — WHERE BETWEEN
- `whereNotBetween(string $column, mixed $min, mixed $max, string $boolean = 'AND')` — WHERE NOT BETWEEN
- `whereNull(string $column, string $boolean = 'AND')` — WHERE IS NULL
- `whereNotNull(string $column, string $boolean = 'AND')` — WHERE IS NOT NULL
- `whereLike(string $column, string $value, string $boolean = 'AND')` — WHERE LIKE
- `whereNotLike(string $column, string $value, string $boolean = 'AND')` — WHERE NOT LIKE
- `whereInSubQuery(string $column, callable|QueryBuilder $query)` — WHERE IN (subquery)
- `whereNotInSubQuery(string $column, callable|QueryBuilder $query)` — WHERE NOT IN (subquery)

### Join Methods

- `join(string $table, string $first, string $operator, string $second, string $type = 'INNER')` — INNER JOIN
- `leftJoin(string $table, string $first, string $operator, string $second)` — LEFT JOIN
- `rightJoin(string $table, string $first, string $operator, string $second)` — RIGHT JOIN
- `joinOn(string $first, string $operator, string $second, string $boolean = 'AND')` — Add extra ON condition to the last join
- `joinSubQuery(callable|QueryBuilder $query, string $alias, string $first, string $operator, string $second, string $type = 'INNER')` — Join with subquery
- `leftJoinSubQuery(callable|QueryBuilder $query, string $alias, string $first, string $operator, string $second)` — Left join with subquery

### Ordering and Grouping Methods

- `orderBy(string $column, string $direction = 'ASC')` — Add ORDER BY clause
- `groupBy(string|array $columns)` — Add GROUP BY clause
- `having(string $condition)` — Add HAVING clause
- `limit(int $limit)` — Set LIMIT
- `offset(int $offset)` — Set OFFSET

### Advanced Methods

- `fromSubQuery(callable|QueryBuilder $query, string $alias)` — Use a subquery as the FROM source
- `with(string $name, callable|QueryBuilder $query)` — Add CTE
- `withRecursive(string $name, callable|QueryBuilder $query)` — Add recursive CTE
- `union(callable|QueryBuilder $query, bool $all = false)` — Add UNION
- `unionAll(callable|QueryBuilder $query)` — Add UNION ALL
- `subQuery(callable $callback): QueryBuilder` — Create a reusable subquery

### Data Manipulation Methods

- `insert(array $data)` — Build INSERT query
- `update(array $data)` — Build UPDATE query
- `delete()` — Build DELETE query

### Utility Methods

- `reset()` — Reset all query components
- `toSql()` — Get the raw SQL string (without parameters)
- `build()` — Get the full query array

## Return Format

All `build()`, `insert()`, `update()`, and `delete()` calls return an array with the following structure:

```php
[
    'sql'    => 'SELECT * FROM users WHERE status = ?',
    'types'  => 's',
    'params' => ['active']
]
```

The `types` string uses MySQLi binding notation: `i` for integers, `d` for floats/doubles, and `s` for strings.

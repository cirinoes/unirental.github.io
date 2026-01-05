<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function seed_database(): void {
    $pdo = db();
    
    echo "Seeding database...\n";
    
    // Seed Users
    $users = [
        ['john.doe@student.edu', 'John Doe', 'member'],
        ['jane.smith@student.edu', 'Jane Smith', 'member'],
        ['mike.wilson@student.edu', 'Mike Wilson', 'member'],
        ['sarah.jones@student.edu', 'Sarah Jones', 'member'],
        ['alex.brown@student.edu', 'Alex Brown', 'member'],
    ];
    
    foreach ($users as [$email, $name, $role]) {
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email=? LIMIT 1');
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            $ins = $pdo->prepare('INSERT INTO users (email, full_name, password_hash, role, created_at) VALUES (?,?,?,?,?)');
            $ins->execute([$email, $name, password_hash('password123', PASSWORD_DEFAULT), $role, now_str()]);
            echo "Created user: $name\n";
        }
    }
    
    // Seed Items
    $items = [
        // Vehicles
        ['Honda Civic 2020', 'Vehicle', 'QAA5043R', 85.00, 'QAA5043R.jpg'],
        ['Toyota Vios 2019', 'Vehicle', 'QAA6712', 75.00, 'QAA6712.jpg'],
        ['Perodua Myvi 2021', 'Vehicle', 'QAB1763C', 65.00, 'QAB1763C.jpg'],
        ['Nissan Almera 2020', 'Vehicle', 'QAB410E', 80.00, 'QAB410E.jpg'],
        ['Proton Saga 2019', 'Vehicle', 'QAB4467', 60.00, 'QAB4467.jpg'],
        ['Honda City 2021', 'Vehicle', 'QAB5489', 90.00, 'QAB5489.jpg'],
        ['Perodua Axia 2020', 'Vehicle', 'QAZ5566', 55.00, 'QAZ5566.jpg'],
        
        // Electronics
        ['MacBook Pro 13"', 'Electronics', 'MBP13001', 45.00, null],
        ['iPad Air', 'Electronics', 'IPAD001', 25.00, null],
        ['Canon DSLR Camera', 'Electronics', 'CAM001', 35.00, null],
        ['Gaming Laptop', 'Electronics', 'GAM001', 50.00, null],
        ['Projector', 'Electronics', 'PROJ001', 30.00, null],
        
        // Sports Equipment
        ['Mountain Bike', 'Sports', 'BIKE001', 20.00, null],
        ['Tennis Racket Set', 'Sports', 'TEN001', 15.00, null],
        ['Basketball', 'Sports', 'BALL001', 8.00, null],
        ['Camping Tent (4-person)', 'Sports', 'TENT001', 25.00, null],
        ['Hiking Backpack', 'Sports', 'BAG001', 12.00, null],
        
        // Tools
        ['Power Drill Set', 'Tools', 'DRILL001', 18.00, null],
        ['Toolbox Complete', 'Tools', 'TOOL001', 22.00, null],
        ['Ladder (6ft)', 'Tools', 'LAD001', 15.00, null],
        
        // Furniture
        ['Folding Table', 'Furniture', 'TAB001', 10.00, null],
        ['Office Chair', 'Furniture', 'CHAIR001', 12.00, null],
        ['Study Desk', 'Furniture', 'DESK001', 15.00, null],
    ];
    
    foreach ($items as [$name, $category, $sku, $rate, $image]) {
        $stmt = $pdo->prepare('SELECT 1 FROM items WHERE sku=? LIMIT 1');
        $stmt->execute([$sku]);
        if (!$stmt->fetch()) {
            $ins = $pdo->prepare('INSERT INTO items (name, category, sku, daily_rate, image_filename) VALUES (?,?,?,?,?)');
            $ins->execute([$name, $category, $sku, $rate, $image]);
            echo "Created item: $name\n";
        }
    }
    
    // Seed some sample transactions and rentals
    $userIds = $pdo->query('SELECT id FROM users WHERE role="member" ORDER BY id LIMIT 3')->fetchAll(PDO::FETCH_COLUMN);
    $itemIds = $pdo->query('SELECT id FROM items ORDER BY id LIMIT 5')->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($userIds) && !empty($itemIds)) {
        // Create sample transactions
        $transactions = [
            [$userIds[0], '2024-01-15 10:30:00', 'PAID', 'CREDIT_CARD', 'TXN001', 170.00],
            [$userIds[1], '2024-01-20 14:15:00', 'PAID', 'ONLINE_BANKING', 'TXN002', 225.00],
            [$userIds[2], '2024-01-25 09:45:00', 'PAID', 'EWALLET', 'TXN003', 120.00],
        ];
        
        foreach ($transactions as $i => [$userId, $createdAt, $status, $method, $ref, $amount]) {
            $stmt = $pdo->prepare('SELECT 1 FROM transactions WHERE payment_ref=? LIMIT 1');
            $stmt->execute([$ref]);
            if (!$stmt->fetch()) {
                $ins = $pdo->prepare('INSERT INTO transactions (user_id, created_at, status, payment_method, payment_ref, total_amount, email_sent) VALUES (?,?,?,?,?,?,?)');
                $ins->execute([$userId, $createdAt, $status, $method, $ref, $amount, 1]);
                $txnId = $pdo->lastInsertId();
                
                // Add transaction items
                $itemId = $itemIds[$i];
                $item = $pdo->prepare('SELECT daily_rate FROM items WHERE id=?');
                $item->execute([$itemId]);
                $dailyRate = $item->fetchColumn();
                
                $days = ($i + 1) * 2; // 2, 4, 6 days
                $subtotal = $dailyRate * $days;
                
                $startDate = date('Y-m-d', strtotime($createdAt . ' +1 day'));
                $endDate = date('Y-m-d', strtotime($startDate . " +{$days} days"));
                
                $ins = $pdo->prepare('INSERT INTO transaction_items (transaction_id, item_id, unit_price, quantity, subtotal, start_date, end_date) VALUES (?,?,?,?,?,?,?)');
                $ins->execute([$txnId, $itemId, $dailyRate, 1, $subtotal, $startDate, $endDate]);
                
                // Create corresponding rental
                $ins = $pdo->prepare('INSERT INTO rentals (user_id, item_id, start_date, end_date, cost, status, created_at) VALUES (?,?,?,?,?,?,?)');
                $ins->execute([$userId, $itemId, $startDate, $endDate, $subtotal, 'Paid', $createdAt]);
                
                echo "Created transaction: $ref\n";
            }
        }
    }
    
    echo "Database seeding completed!\n";
}

// Run seeding
try {
    seed_database();
} catch (Exception $e) {
    echo "Error seeding database: " . $e->getMessage() . "\n";
}
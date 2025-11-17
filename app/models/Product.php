<?php
class Product
{
    /**
     * Find all available products, optionally by category.
     */
    public static function findAllAvailable(mysqli $conn, array $filters = []): array
    {
        $sql = "SELECT p.*, u.name as seller_name, u.email as seller_email, u.phone as seller_phone 
                FROM products p
                JOIN users u ON p.seller_id = u.user_id WHERE p.status = 'available'";
        
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $sql .= " AND p.title LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
            $types .= 's';
        }

        if (!empty($filters['categories']) && is_array($filters['categories'])) {
            $placeholders = implode(',', array_fill(0, count($filters['categories']), '?'));
            $sql .= " AND p.category IN ($placeholders)";
            $params = array_merge($params, $filters['categories']);
            $types .= str_repeat('s', count($filters['categories']));
        }

        if (!empty($filters['conditions']) && is_array($filters['conditions'])) {
            $placeholders = implode(',', array_fill(0, count($filters['conditions']), '?'));
            $sql .= " AND p.product_condition IN ($placeholders)";
            $params = array_merge($params, $filters['conditions']);
            $types .= str_repeat('s', count($filters['conditions']));
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = (float)$filters['max_price'];
            $types .= 'd';
        }

        // Sorting logic
        $sort_order = 'p.created_at DESC'; // Default sort
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price-asc':
                    $sort_order = 'p.price ASC';
                    break;
                case 'price-desc':
                    $sort_order = 'p.price DESC';
                    break;
            }
        }
        $sql .= " ORDER BY $sort_order";

        $stmt = $conn->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Find a single product by its ID.
     */
    public static function findById(mysqli $conn, int $productId): ?array
    {
        $sql = "SELECT p.*, u.name as seller_name, u.email as seller_email, u.phone as seller_phone 
                FROM products p
                JOIN users u ON p.seller_id = u.user_id
                WHERE p.product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result ? $result->fetch_assoc() : null;
    }

    /**
     * Create a new product listing.
     */
    public static function create(mysqli $conn, array $data): bool|string
    {
        $sql = "INSERT INTO products (seller_id, title, description, price, category, product_condition, image_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return "Prepare failed: " . $conn->error;
        }
        $stmt->bind_param("issdsss", $data['seller_id'], $data['title'], $data['description'], $data['price'], $data['category'], $data['product_condition'], $data['image_url']);
        return $stmt->execute();
    }

    /**
     * Update an existing product listing.
     */
    public static function update(mysqli $conn, int $productId, array $data): bool|string
    {
        $sql = "UPDATE products SET title = ?, description = ?, price = ?, category = ?, product_condition = ?, image_url = ?, status = ? 
                WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return "Prepare failed: " . $conn->error;
        }
        $stmt->bind_param("ssdssssi", $data['title'], $data['description'], $data['price'], $data['category'], $data['product_condition'], $data['image_url'], $data['status'], $productId);
        return $stmt->execute();
    }
}
?>
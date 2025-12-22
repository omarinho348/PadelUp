<?php

class MenuItem
{
    public static function fetchActive(mysqli $conn): array
    {
        $sql = "SELECT id, parent_id, title, url, display_order FROM menu_items WHERE is_active = 1 ORDER BY parent_id ASC, display_order ASC, id ASC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { return []; }
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows ?: [];
    }

    public static function getTree(mysqli $conn): array
    {
        $items = self::fetchActive($conn);
        // Index by parent_id
        $byParent = [];
        foreach ($items as $item) {
            $pid = $item['parent_id'] ?? null;
            if (!isset($byParent[$pid])) { $byParent[$pid] = []; }
            $byParent[$pid][] = $item;
        }
        // Build tree from top-level (parent_id NULL)
        return self::buildChildren($byParent, null, 0, []);
    }

    private static function buildChildren(array $byParent, $parentId, int $depth, array $ancestors): array
    {
        // Safeguard against pathological cycles
        if ($depth > 20) { return []; }
        if (in_array($parentId, $ancestors, true)) { return []; }
        $children = $byParent[$parentId] ?? [];
        $result = [];
        foreach ($children as $child) {
            $node = $child;
            $node['children'] = self::buildChildren($byParent, $child['id'], $depth + 1, array_merge($ancestors, [$parentId]));
            $result[] = $node;
        }
        return $result;
    }
}

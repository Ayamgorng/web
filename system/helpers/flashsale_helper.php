<?php
defined("BASEPATH") or exit("No direct script access allowed.");

class FlashSaleManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create a new flash sale
     */
    public function createFlashSale($data) {
        $stmt = $this->db->prepare("
            INSERT INTO flash_sales (title, description, discount_type, discount_value, start_time, end_time, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['discount_type'],
            $data['discount_value'],
            $data['start_time'],
            $data['end_time'],
            $data['created_by']
        ]);
    }
    
    /**
     * Add product to flash sale
     */
    public function addProductToFlashSale($flash_sale_id, $product_data) {
        $stmt = $this->db->prepare("
            INSERT INTO flash_sale_products (flash_sale_id, product_type, product_id, original_price, sale_price, stock_limit) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $flash_sale_id,
            $product_data['product_type'],
            $product_data['product_id'],
            $product_data['original_price'],
            $product_data['sale_price'],
            $product_data['stock_limit'] ?? null
        ]);
    }
    
    /**
     * Get active flash sales
     */
    public function getActiveFlashSales() {
        $stmt = $this->db->prepare("
            SELECT fs.*, 
                   COUNT(fsp.id) as product_count,
                   CASE 
                       WHEN NOW() < fs.start_time THEN 'upcoming'
                       WHEN NOW() BETWEEN fs.start_time AND fs.end_time THEN 'active'
                       ELSE 'ended'
                   END as current_status
            FROM flash_sales fs
            LEFT JOIN flash_sale_products fsp ON fs.id = fsp.flash_sale_id
            WHERE fs.status IN ('scheduled', 'active') 
            AND fs.end_time > NOW()
            GROUP BY fs.id
            ORDER BY fs.start_time ASC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get flash sale products
     */
    public function getFlashSaleProducts($flash_sale_id) {
        $stmt = $this->db->prepare("
            SELECT fsp.*, 
                   CASE fsp.product_type
                       WHEN 'pulsa' THEN (SELECT layanan FROM layanan_pulsa WHERE id = fsp.product_id)
                       WHEN 'games' THEN (SELECT layanan FROM layanan_sosmed WHERE id = fsp.product_id AND kategori = 'Games')
                       WHEN 'sosmed' THEN (SELECT layanan FROM layanan_sosmed WHERE id = fsp.product_id)
                   END as product_name,
                   CASE fsp.product_type
                       WHEN 'pulsa' THEN (SELECT provider FROM layanan_pulsa WHERE id = fsp.product_id)
                       WHEN 'games' THEN (SELECT provider FROM layanan_sosmed WHERE id = fsp.product_id AND kategori = 'Games')
                       WHEN 'sosmed' THEN (SELECT provider FROM layanan_sosmed WHERE id = fsp.product_id)
                   END as provider_name
            FROM flash_sale_products fsp
            WHERE fsp.flash_sale_id = ?
            AND (fsp.stock_limit IS NULL OR fsp.sold_count < fsp.stock_limit)
        ");
        
        $stmt->execute([$flash_sale_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if product is in flash sale
     */
    public function isProductInFlashSale($product_type, $product_id) {
        $stmt = $this->db->prepare("
            SELECT fsp.*, fs.title, fs.discount_type, fs.discount_value
            FROM flash_sale_products fsp
            JOIN flash_sales fs ON fsp.flash_sale_id = fs.id
            WHERE fsp.product_type = ? AND fsp.product_id = ?
            AND fs.status = 'active'
            AND NOW() BETWEEN fs.start_time AND fs.end_time
            AND (fsp.stock_limit IS NULL OR fsp.sold_count < fsp.stock_limit)
            LIMIT 1
        ");
        
        $stmt->execute([$product_type, $product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update flash sale status
     */
    public function updateFlashSaleStatus() {
        // Update to active
        $stmt = $this->db->prepare("
            UPDATE flash_sales 
            SET status = 'active' 
            WHERE status = 'scheduled' 
            AND start_time <= NOW() 
            AND end_time > NOW()
        ");
        $stmt->execute();
        
        // Update to ended
        $stmt = $this->db->prepare("
            UPDATE flash_sales 
            SET status = 'ended' 
            WHERE status = 'active' 
            AND end_time <= NOW()
        ");
        $stmt->execute();
    }
    
    /**
     * Record flash sale purchase
     */
    public function recordFlashSalePurchase($flash_sale_product_id) {
        $stmt = $this->db->prepare("
            UPDATE flash_sale_products 
            SET sold_count = sold_count + 1 
            WHERE id = ?
        ");
        return $stmt->execute([$flash_sale_product_id]);
    }
    
    /**
     * Get flash sale statistics
     */
    public function getFlashSaleStats($flash_sale_id) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_products,
                SUM(sold_count) as total_sold,
                SUM(CASE WHEN stock_limit IS NOT NULL THEN stock_limit ELSE 0 END) as total_stock,
                SUM((original_price - sale_price) * sold_count) as total_discount_given
            FROM flash_sale_products 
            WHERE flash_sale_id = ?
        ");
        $stmt->execute([$flash_sale_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get time remaining for flash sale
     */
    public function getTimeRemaining($flash_sale) {
        $now = new DateTime();
        $start = new DateTime($flash_sale['start_time']);
        $end = new DateTime($flash_sale['end_time']);
        
        if ($now < $start) {
            $diff = $start->diff($now);
            return [
                'status' => 'upcoming',
                'days' => $diff->days,
                'hours' => $diff->h,
                'minutes' => $diff->i,
                'seconds' => $diff->s
            ];
        } elseif ($now >= $start && $now <= $end) {
            $diff = $end->diff($now);
            return [
                'status' => 'active',
                'days' => $diff->days,
                'hours' => $diff->h,
                'minutes' => $diff->i,
                'seconds' => $diff->s
            ];
        } else {
            return ['status' => 'ended'];
        }
    }
}

// Helper functions for flash sale system
function get_flashsale_manager() {
    global $db;
    return new FlashSaleManager($db);
}

function get_active_flash_sales() {
    $fsm = get_flashsale_manager();
    $fsm->updateFlashSaleStatus(); // Update status first
    return $fsm->getActiveFlashSales();
}

function check_flash_sale_price($product_type, $product_id, $original_price) {
    $fsm = get_flashsale_manager();
    $flash_sale = $fsm->isProductInFlashSale($product_type, $product_id);
    
    if ($flash_sale) {
        return [
            'is_flash_sale' => true,
            'original_price' => $original_price,
            'sale_price' => $flash_sale['sale_price'],
            'discount_amount' => $original_price - $flash_sale['sale_price'],
            'flash_sale_id' => $flash_sale['flash_sale_id'],
            'flash_sale_product_id' => $flash_sale['id']
        ];
    }
    
    return [
        'is_flash_sale' => false,
        'original_price' => $original_price,
        'sale_price' => $original_price
    ];
}

function format_time_remaining($time_data) {
    if ($time_data['status'] === 'ended') {
        return 'Flash Sale Berakhir';
    }
    
    $parts = [];
    if ($time_data['days'] > 0) $parts[] = $time_data['days'] . 'd';
    if ($time_data['hours'] > 0) $parts[] = $time_data['hours'] . 'h';
    if ($time_data['minutes'] > 0) $parts[] = $time_data['minutes'] . 'm';
    if ($time_data['seconds'] > 0) $parts[] = $time_data['seconds'] . 's';
    
    $prefix = $time_data['status'] === 'upcoming' ? 'Dimulai dalam: ' : 'Berakhir dalam: ';
    return $prefix . implode(' ', $parts);
}

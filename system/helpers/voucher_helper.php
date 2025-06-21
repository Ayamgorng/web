<?php
defined("BASEPATH") or exit("No direct script access allowed.");

class VoucherManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create a new voucher
     */
    public function createVoucher($data) {
        $stmt = $this->db->prepare("
            INSERT INTO vouchers (code, type, value, min_purchase, max_discount, usage_limit, valid_from, valid_until, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['code'],
            $data['type'],
            $data['value'],
            $data['min_purchase'] ?? 0,
            $data['max_discount'] ?? null,
            $data['usage_limit'] ?? null,
            $data['valid_from'],
            $data['valid_until'],
            $data['created_by']
        ]);
    }
    
    /**
     * Validate voucher code
     */
    public function validateVoucher($code, $purchase_amount = 0) {
        $stmt = $this->db->prepare("
            SELECT * FROM vouchers 
            WHERE code = ? AND status = 'active' 
            AND valid_from <= NOW() AND valid_until >= NOW()
            AND (usage_limit IS NULL OR used_count < usage_limit)
            AND min_purchase <= ?
        ");
        
        $stmt->execute([$code, $purchase_amount]);
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$voucher) {
            return ['valid' => false, 'message' => 'Voucher tidak valid atau sudah expired'];
        }
        
        return ['valid' => true, 'voucher' => $voucher];
    }
    
    /**
     * Calculate discount amount
     */
    public function calculateDiscount($voucher, $purchase_amount) {
        if ($voucher['type'] === 'percentage') {
            $discount = ($purchase_amount * $voucher['value']) / 100;
            if ($voucher['max_discount'] && $discount > $voucher['max_discount']) {
                $discount = $voucher['max_discount'];
            }
        } else {
            $discount = $voucher['value'];
        }
        
        return min($discount, $purchase_amount);
    }
    
    /**
     * Apply voucher to order
     */
    public function applyVoucher($voucher_id, $user_id, $order_id, $discount_amount) {
        $this->db->beginTransaction();
        
        try {
            // Record voucher usage
            $stmt = $this->db->prepare("
                INSERT INTO voucher_usage (voucher_id, user_id, order_id, discount_amount) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$voucher_id, $user_id, $order_id, $discount_amount]);
            
            // Update voucher used count
            $stmt = $this->db->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?");
            $stmt->execute([$voucher_id]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Get active vouchers
     */
    public function getActiveVouchers($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT * FROM vouchers 
            WHERE status = 'active' AND valid_from <= NOW() AND valid_until >= NOW()
            AND (usage_limit IS NULL OR used_count < usage_limit)
            ORDER BY created_at DESC LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generate unique voucher code
     */
    public function generateVoucherCode($prefix = 'VOUCHER', $length = 8) {
        do {
            $code = $prefix . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length));
            $stmt = $this->db->prepare("SELECT id FROM vouchers WHERE code = ?");
            $stmt->execute([$code]);
        } while ($stmt->fetch());
        
        return $code;
    }
    
    /**
     * Get voucher usage statistics
     */
    public function getVoucherStats($voucher_id) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_usage,
                SUM(discount_amount) as total_discount,
                AVG(discount_amount) as avg_discount
            FROM voucher_usage 
            WHERE voucher_id = ?
        ");
        $stmt->execute([$voucher_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Helper functions for voucher system
function get_voucher_manager() {
    global $db;
    return new VoucherManager($db);
}

function validate_voucher_code($code, $purchase_amount = 0) {
    $vm = get_voucher_manager();
    return $vm->validateVoucher($code, $purchase_amount);
}

function apply_voucher_discount($voucher_code, $user_id, $order_id, $purchase_amount) {
    $vm = get_voucher_manager();
    $validation = $vm->validateVoucher($voucher_code, $purchase_amount);
    
    if (!$validation['valid']) {
        return ['success' => false, 'message' => $validation['message']];
    }
    
    $voucher = $validation['voucher'];
    $discount = $vm->calculateDiscount($voucher, $purchase_amount);
    
    if ($vm->applyVoucher($voucher['id'], $user_id, $order_id, $discount)) {
        return [
            'success' => true,
            'discount_amount' => $discount,
            'final_amount' => $purchase_amount - $discount,
            'voucher' => $voucher
        ];
    }
    
    return ['success' => false, 'message' => 'Gagal menerapkan voucher'];
}

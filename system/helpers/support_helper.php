<?php
defined("BASEPATH") or exit("No direct script access allowed.");

class SupportManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create a new support ticket
     */
    public function createTicket($data) {
        $ticket_id = $this->generateTicketId();
        
        $stmt = $this->db->prepare("
            INSERT INTO support_tickets (ticket_id, user_id, subject, category, priority) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $ticket_id,
            $data['user_id'],
            $data['subject'],
            $data['category'] ?? 'general',
            $data['priority'] ?? 'medium'
        ]);
        
        if ($result) {
            $ticket_db_id = $this->db->lastInsertId();
            
            // Add initial message
            $this->addMessage($ticket_db_id, 'user', $data['user_id'], $data['message']);
            
            return $ticket_id;
        }
        
        return false;
    }
    
    /**
     * Add message to ticket
     */
    public function addMessage($ticket_id, $sender_type, $sender_id, $message, $attachments = null) {
        $stmt = $this->db->prepare("
            INSERT INTO support_messages (ticket_id, sender_type, sender_id, message, attachments) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $ticket_id,
            $sender_type,
            $sender_id,
            $message,
            $attachments
        ]);
        
        if ($result && $sender_type === 'admin') {
            // Update ticket status to waiting_customer if admin replied
            $this->updateTicketStatus($ticket_id, 'waiting_customer');
        } elseif ($result && $sender_type === 'user') {
            // Update ticket status to open if user replied
            $this->updateTicketStatus($ticket_id, 'open');
        }
        
        return $result;
    }
    
    /**
     * Get ticket by ID
     */
    public function getTicket($ticket_id) {
        $stmt = $this->db->prepare("
            SELECT st.*, u.username, u.email 
            FROM support_tickets st
            JOIN users u ON st.user_id = u.id
            WHERE st.ticket_id = ?
        ");
        
        $stmt->execute([$ticket_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get ticket messages
     */
    public function getTicketMessages($ticket_id) {
        // Get ticket database ID first
        $stmt = $this->db->prepare("SELECT id FROM support_tickets WHERE ticket_id = ?");
        $stmt->execute([$ticket_id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) return [];
        
        $stmt = $this->db->prepare("
            SELECT sm.*, 
                   CASE 
                       WHEN sm.sender_type = 'user' THEN u.username
                       ELSE sm.sender_id
                   END as sender_name
            FROM support_messages sm
            LEFT JOIN users u ON sm.sender_id = u.id AND sm.sender_type = 'user'
            WHERE sm.ticket_id = ?
            ORDER BY sm.created_at ASC
        ");
        
        $stmt->execute([$ticket['id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user tickets
     */
    public function getUserTickets($user_id, $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT st.*, 
                   (SELECT COUNT(*) FROM support_messages sm WHERE sm.ticket_id = st.id) as message_count,
                   (SELECT sm.created_at FROM support_messages sm WHERE sm.ticket_id = st.id ORDER BY sm.created_at DESC LIMIT 1) as last_message_at
            FROM support_tickets st
            WHERE st.user_id = ?
            ORDER BY st.updated_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all tickets for admin
     */
    public function getAllTickets($status = null, $limit = 50) {
        $sql = "
            SELECT st.*, u.username, u.email,
                   (SELECT COUNT(*) FROM support_messages sm WHERE sm.ticket_id = st.id) as message_count,
                   (SELECT sm.created_at FROM support_messages sm WHERE sm.ticket_id = st.id ORDER BY sm.created_at DESC LIMIT 1) as last_message_at
            FROM support_tickets st
            JOIN users u ON st.user_id = u.id
        ";
        
        $params = [];
        if ($status) {
            $sql .= " WHERE st.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY st.updated_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update ticket status
     */
    public function updateTicketStatus($ticket_id, $status, $assigned_to = null) {
        $sql = "UPDATE support_tickets SET status = ?, updated_at = CURRENT_TIMESTAMP";
        $params = [$status];
        
        if ($assigned_to !== null) {
            $sql .= ", assigned_to = ?";
            $params[] = $assigned_to;
        }
        
        $sql .= " WHERE ticket_id = ?";
        $params[] = $ticket_id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Generate unique ticket ID
     */
    private function generateTicketId() {
        do {
            $ticket_id = 'TKT' . date('Ymd') . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
            $stmt = $this->db->prepare("SELECT id FROM support_tickets WHERE ticket_id = ?");
            $stmt->execute([$ticket_id]);
        } while ($stmt->fetch());
        
        return $ticket_id;
    }
    
    /**
     * Get ticket statistics
     */
    public function getTicketStats() {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_tickets,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tickets,
                SUM(CASE WHEN status = 'waiting_customer' THEN 1 ELSE 0 END) as waiting_customer_tickets,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_tickets,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_tickets,
                AVG(TIMESTAMPDIFF(HOUR, created_at, CASE WHEN status IN ('resolved', 'closed') THEN updated_at ELSE NULL END)) as avg_resolution_time
            FROM support_tickets
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Search tickets
     */
    public function searchTickets($query, $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT st.*, u.username, u.email
            FROM support_tickets st
            JOIN users u ON st.user_id = u.id
            WHERE st.ticket_id LIKE ? 
            OR st.subject LIKE ? 
            OR u.username LIKE ?
            ORDER BY st.updated_at DESC
            LIMIT ?
        ");
        
        $search_term = "%{$query}%";
        $stmt->execute([$search_term, $search_term, $search_term, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Helper functions for support system
function get_support_manager() {
    global $db;
    return new SupportManager($db);
}

function create_support_ticket($user_id, $subject, $message, $category = 'general', $priority = 'medium') {
    $sm = get_support_manager();
    return $sm->createTicket([
        'user_id' => $user_id,
        'subject' => $subject,
        'message' => $message,
        'category' => $category,
        'priority' => $priority
    ]);
}

function get_user_support_tickets($user_id, $limit = 20) {
    $sm = get_support_manager();
    return $sm->getUserTickets($user_id, $limit);
}

function get_ticket_details($ticket_id) {
    $sm = get_support_manager();
    $ticket = $sm->getTicket($ticket_id);
    if ($ticket) {
        $ticket['messages'] = $sm->getTicketMessages($ticket_id);
    }
    return $ticket;
}

function add_ticket_reply($ticket_id, $sender_type, $sender_id, $message) {
    $sm = get_support_manager();
    
    // Get ticket database ID
    $ticket = $sm->getTicket($ticket_id);
    if (!$ticket) return false;
    
    return $sm->addMessage($ticket['id'], $sender_type, $sender_id, $message);
}

function get_support_categories() {
    return [
        'general' => 'Umum',
        'technical' => 'Teknis',
        'billing' => 'Pembayaran',
        'complaint' => 'Keluhan',
        'suggestion' => 'Saran'
    ];
}

function get_support_priorities() {
    return [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        'urgent' => 'Mendesak'
    ];
}

function get_support_statuses() {
    return [
        'open' => 'Terbuka',
        'in_progress' => 'Sedang Diproses',
        'waiting_customer' => 'Menunggu Customer',
        'resolved' => 'Selesai',
        'closed' => 'Ditutup'
    ];
}

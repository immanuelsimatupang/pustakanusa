<?php
// File ini adalah endpoint untuk webhook notifikasi Midtrans

// Include konfigurasi
require_once 'config/database.php';
require_once 'config/midtrans.php';

// Log notifikasi
$raw_post_data = file_get_contents('php://input');
$log_file = 'logs/midtrans-' . date('Y-m-d') . '.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $raw_post_data . PHP_EOL, FILE_APPEND);

try {
    // Proses notifikasi dari Midtrans
    $notification = handleMidtransNotification();
    
    if ($notification && isset($notification['order_id']) && isset($notification['payment_status'])) {
        // Update status pembayaran di database
        $order_number = $notification['order_id'];
        $payment_status = $notification['payment_status'];
        
        // Update status pembayaran
        updatePaymentStatus($order_number, $payment_status);
        
        // Log hasil
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Order: $order_number, Status: $payment_status" . PHP_EOL, FILE_APPEND);
        
        // Kirim notifikasi email ke pelanggan
        sendPaymentNotificationEmail($order_number, $payment_status);
        
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    } else {
        // Gagal memproses notifikasi
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Failed to process notification: Invalid data format" . PHP_EOL, FILE_APPEND);
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid notification data']);
    }
} catch (Exception $e) {
    // Log error
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

/**
 * Kirim email notifikasi pembayaran ke pelanggan
 * 
 * @param string $order_number Nomor pesanan
 * @param string $payment_status Status pembayaran
 * @return bool Status pengiriman email
 */
function sendPaymentNotificationEmail($order_number, $payment_status)
{
    global $conn;
    
    // Ambil data pesanan
    $sql = "SELECT * FROM orders WHERE order_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $order_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        $customer_email = $order['customer_email'];
        $customer_name = $order['customer_name'];
        
        // Siapkan pesan email
        $subject = '';
        $message = '';
        
        switch ($payment_status) {
            case 'paid':
                $subject = "Pembayaran Berhasil untuk Pesanan #$order_number";
                $message = "Halo $customer_name,\n\n";
                $message .= "Pembayaran Anda untuk pesanan #$order_number telah kami terima.\n";
                $message .= "Pesanan Anda sedang kami proses dan akan segera dikirimkan.\n\n";
                $message .= "Terima kasih telah berbelanja di Pustakanusa!\n\n";
                $message .= "Salam,\nTim Pustakanusa";
                break;
            
            case 'failed':
                $subject = "Pembayaran Gagal untuk Pesanan #$order_number";
                $message = "Halo $customer_name,\n\n";
                $message .= "Sayang sekali, pembayaran Anda untuk pesanan #$order_number gagal diproses.\n";
                $message .= "Silakan coba lagi atau gunakan metode pembayaran lain.\n\n";
                $message .= "Jika Anda memerlukan bantuan, silakan hubungi tim layanan pelanggan kami.\n\n";
                $message .= "Salam,\nTim Pustakanusa";
                break;
                
            case 'pending':
                $subject = "Menunggu Pembayaran untuk Pesanan #$order_number";
                $message = "Halo $customer_name,\n\n";
                $message .= "Pesanan #$order_number Anda telah kami terima dan sedang menunggu pembayaran.\n";
                $message .= "Mohon selesaikan pembayaran Anda sesuai instruksi.\n\n";
                $message .= "Jika Anda telah melakukan pembayaran, mohon tunggu beberapa saat untuk verifikasi.\n\n";
                $message .= "Salam,\nTim Pustakanusa";
                break;
        }
        
        // Kirim email
        $headers = "From: Pustakanusa <no-reply@pustakanusa.com>\r\n";
        $headers .= "Reply-To: Layanan Pelanggan <cs@pustakanusa.com>\r\n";
        
        return mail($customer_email, $subject, $message, $headers);
    }
    
    return false;
}
?> 
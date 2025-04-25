<?php
// Konfigurasi Midtrans
define('MIDTRANS_SERVER_KEY', 'your_server_key_here'); // Ganti dengan Server Key Anda
define('MIDTRANS_CLIENT_KEY', 'your_client_key_here'); // Ganti dengan Client Key Anda
define('MIDTRANS_IS_PRODUCTION', false); // Set true untuk production
define('MIDTRANS_SANITIZE', true);
define('MIDTRANS_3DS', true);

// Include Midtrans PHP Library (https://github.com/Midtrans/midtrans-php)
// Pastikan Anda telah mengunduh dan menyimpan library ini di folder yang sesuai
require_once __DIR__ . '/../vendor/midtrans/midtrans-php/Midtrans.php';

// Setup konfigurasi Midtrans
\Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
\Midtrans\Config::$isProduction = MIDTRANS_IS_PRODUCTION;
\Midtrans\Config::$isSanitized = MIDTRANS_SANITIZE;
\Midtrans\Config::$is3ds = MIDTRANS_3DS;

/**
 * Membuat token transaksi Midtrans Snap
 * 
 * @param array $order Data pesanan
 * @param array $items Item pesanan
 * @param array $customer Data pelanggan
 * @return string Token transaksi
 */
function getMidtransSnapToken($order, $items, $customer) {
    // Persiapkan item details
    $item_details = [];
    
    foreach ($items as $item) {
        $item_details[] = [
            'id' => $item['book_id'],
            'price' => $item['price'],
            'quantity' => $item['qty'],
            'name' => substr($item['title'], 0, 50) // Batas 50 karakter
        ];
    }
    
    // Tambahkan biaya pengiriman sebagai item
    if ($order['shipping_cost'] > 0) {
        $item_details[] = [
            'id' => 'SHIPPING',
            'price' => $order['shipping_cost'],
            'quantity' => 1,
            'name' => 'Biaya Pengiriman'
        ];
    }
    
    // Tambahkan diskon (jika ada) sebagai item dengan harga negatif
    if ($order['discount_amount'] > 0) {
        $item_details[] = [
            'id' => 'DISCOUNT',
            'price' => -$order['discount_amount'],
            'quantity' => 1,
            'name' => 'Diskon' . ($order['coupon_code'] ? ' (' . $order['coupon_code'] . ')' : '')
        ];
    }
    
    // Parameter transaksi
    $transaction_details = [
        'order_id' => $order['order_number'],
        'gross_amount' => $order['total_price']
    ];
    
    // Data pelanggan
    $customer_details = [
        'first_name' => $customer['name'],
        'email' => $customer['email'],
        'phone' => $customer['phone'],
        'billing_address' => [
            'first_name' => $customer['name'],
            'email' => $customer['email'],
            'phone' => $customer['phone'],
            'address' => $customer['address'],
            'city' => $customer['city'],
            'postal_code' => $customer['postal_code'],
            'country_code' => 'IDN'
        ],
        'shipping_address' => [
            'first_name' => $customer['name'],
            'email' => $customer['email'],
            'phone' => $customer['phone'],
            'address' => $customer['address'],
            'city' => $customer['city'],
            'postal_code' => $customer['postal_code'],
            'country_code' => 'IDN'
        ]
    ];
    
    // Opsi enable payments berdasarkan metode pembayaran yang dipilih
    $enable_payments = [];
    switch ($order['payment_method']) {
        case 'bank_transfer':
            $enable_payments = ['bank_transfer', 'bca_va', 'bni_va', 'bri_va', 'mandiri_va'];
            break;
        case 'ewallet':
            $enable_payments = ['gopay', 'shopeepay', 'qris'];
            break;
        case 'credit_card':
            $enable_payments = ['credit_card'];
            break;
        default:
            $enable_payments = ['bank_transfer', 'bca_va', 'bni_va', 'bri_va', 'mandiri_va', 'gopay', 'shopeepay', 'qris', 'credit_card'];
            break;
    }
    
    // Parameter untuk Snap
    $params = [
        'transaction_details' => $transaction_details,
        'item_details' => $item_details,
        'customer_details' => $customer_details,
        'enabled_payments' => $enable_payments,
        'callbacks' => [
            'finish' => 'https://pustakanusa.com/payment-success.php?order=' . $order['order_number'],
        ]
    ];
    
    try {
        // Dapatkan Token Snap
        $snapToken = \Midtrans\Snap::getSnapToken($params);
        return $snapToken;
    } catch (\Exception $e) {
        error_log('Midtrans Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Memproses notifikasi pembayaran dari Midtrans
 * 
 * @return array Status pembayaran
 */
function handleMidtransNotification() {
    try {
        $notification = new \Midtrans\Notification();
        
        $transaction = $notification->transaction_status;
        $type = $notification->payment_type;
        $order_id = $notification->order_id;
        $fraud = $notification->fraud_status;
        
        if ($transaction == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    // Transaksi ditandai sebagai challenge oleh sistem anti-fraud
                    return [
                        'order_id' => $order_id,
                        'status' => 'challenge',
                        'payment_status' => 'pending'
                    ];
                } else {
                    // Pembayaran berhasil
                    return [
                        'order_id' => $order_id,
                        'status' => 'success',
                        'payment_status' => 'paid'
                    ];
                }
            }
        } else if ($transaction == 'settlement') {
            // Pembayaran berhasil (untuk metode non-kartu kredit)
            return [
                'order_id' => $order_id,
                'status' => 'success',
                'payment_status' => 'paid'
            ];
        } else if ($transaction == 'pending') {
            // Transaksi pending
            return [
                'order_id' => $order_id,
                'status' => 'pending',
                'payment_status' => 'pending'
            ];
        } else if ($transaction == 'deny' || $transaction == 'cancel' || $transaction == 'expire') {
            // Pembayaran gagal
            return [
                'order_id' => $order_id,
                'status' => 'failed',
                'payment_status' => 'failed'
            ];
        }
        
        // Status tidak dikenal
        return [
            'order_id' => $order_id,
            'status' => 'unknown',
            'payment_status' => 'pending'
        ];
    } catch (\Exception $e) {
        error_log('Midtrans Notification Error: ' . $e->getMessage());
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

/**
 * Mengupdate status pembayaran pesanan
 * 
 * @param string $order_number Nomor pesanan
 * @param string $payment_status Status pembayaran (paid, pending, failed, cancelled)
 * @return bool True jika berhasil, false jika gagal
 */
function updatePaymentStatus($order_number, $payment_status) {
    global $conn;
    
    $order_status = 'pending';
    if ($payment_status == 'paid') {
        $order_status = 'processing';
    } else if ($payment_status == 'failed' || $payment_status == 'cancelled') {
        $order_status = 'cancelled';
    }
    
    $sql = "UPDATE orders SET payment_status = ?, order_status = ? WHERE order_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $payment_status, $order_status, $order_number);
    
    return $stmt->execute();
}
?> 
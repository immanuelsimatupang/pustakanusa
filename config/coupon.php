<?php
// Include database connection jika belum di-include
if (!isset($conn)) {
    require_once 'database.php';
}

/**
 * Memeriksa apakah kupon valid
 * 
 * @param string $coupon_code Kode kupon
 * @param float $cart_total Total belanja
 * @return array|bool Info kupon jika valid, false jika tidak valid
 */
function validateCoupon($coupon_code, $cart_total) {
    global $conn;
    
    $sql = "SELECT * FROM coupons 
            WHERE code = ? 
            AND is_active = 1 
            AND (start_date IS NULL OR start_date <= CURDATE()) 
            AND (end_date IS NULL OR end_date >= CURDATE())
            AND (usage_limit IS NULL OR used_count < usage_limit)
            AND min_purchase <= ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sd", $coupon_code, $cart_total);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Menghitung jumlah diskon berdasarkan kupon
 * 
 * @param array $coupon Info kupon
 * @param float $cart_total Total belanja
 * @return float Jumlah diskon
 */
function calculateDiscount($coupon, $cart_total) {
    if (!$coupon) {
        return 0;
    }
    
    $discount_amount = 0;
    
    if ($coupon['discount_type'] == 'percentage') {
        // Diskon persentase dari total belanja
        $discount_amount = $cart_total * ($coupon['discount_value'] / 100);
        
        // Terapkan maksimum diskon jika ada
        if ($coupon['max_discount'] !== null) {
            $discount_amount = min($discount_amount, $coupon['max_discount']);
        }
    } else {
        // Diskon tetap
        $discount_amount = $coupon['discount_value'];
        
        // Diskon tidak boleh melebihi total belanja
        $discount_amount = min($discount_amount, $cart_total);
    }
    
    return $discount_amount;
}

/**
 * Mengupdate penggunaan kupon
 * 
 * @param string $coupon_code Kode kupon
 * @return bool True jika berhasil, false jika gagal
 */
function updateCouponUsage($coupon_code) {
    global $conn;
    
    $sql = "UPDATE coupons SET used_count = used_count + 1 WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $coupon_code);
    
    return $stmt->execute();
}

/**
 * Mendapatkan format tampilan diskon
 * 
 * @param array $coupon Info kupon
 * @return string Format tampilan diskon
 */
function getDiscountDisplay($coupon) {
    if (!$coupon) {
        return '';
    }
    
    if ($coupon['discount_type'] == 'percentage') {
        return $coupon['discount_value'] . '%';
    } else {
        return formatRupiah($coupon['discount_value']);
    }
}
?> 
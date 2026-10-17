<?php
/**
 * Kravyo - Razorpay Service
 * Handles Razorpay API operations — specifically automated refund processing.
 *
 * Usage:
 *   require_once APP_PATH . '/services/RazorpayService.php';
 *   $result = RazorpayService::processRefund('pay_XXXXX', 71000);
 *   if ($result['success']) { // refund initiated }
 */

class RazorpayService {

    /**
     * Process a full refund for a given Razorpay payment.
     *
     * @param  string $razorpayPaymentId  The original Razorpay payment_id (e.g. "pay_XXXXX")
     * @param  int    $amountPaise        Amount to refund in paise (rupees × 100).
     *                                   Pass 0 to trigger a full refund automatically.
     * @return array {
     *   success: bool,
     *   refund_id: string|null,   — Razorpay refund ID if successful (e.g. "rfnd_XXXXX")
     *   error: string|null        — Human-readable error message if failed
     * }
     */
    public static function processRefund(string $razorpayPaymentId, int $amountPaise = 0): array {
        require_once CONFIG_PATH . '/razorpay.php';

        if (empty($razorpayPaymentId)) {
            return ['success' => false, 'refund_id' => null, 'error' => 'No Razorpay payment ID provided.'];
        }

        $url  = 'https://api.razorpay.com/v1/payments/' . $razorpayPaymentId . '/refund';
        $body = [];

        // Only specify amount for partial refunds; omit for full refund
        if ($amountPaise > 0) {
            $body['amount'] = $amountPaise;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD,        RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,  true);
        curl_setopt($ch, CURLOPT_POST,            true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,      json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER,      ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT,         15);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Network-level failure
        if ($response === false || !empty($curlError)) {
            error_log('[Kravyo][RazorpayService] cURL error for ' . $razorpayPaymentId . ': ' . $curlError);
            return ['success' => false, 'refund_id' => null, 'error' => 'Network error while contacting Razorpay: ' . $curlError];
        }

        $data = json_decode($response, true);

        // Razorpay returns 200 on success with an "id" field (the refund ID)
        if ($httpCode === 200 && isset($data['id'])) {
            error_log('[Kravyo][RazorpayService] Refund SUCCESS for payment ' . $razorpayPaymentId . ' → refund_id: ' . $data['id']);
            return ['success' => true, 'refund_id' => $data['id'], 'error' => null];
        }

        // API-level failure — Razorpay returns an error object
        $errorDesc = $data['error']['description'] ?? ('Razorpay returned HTTP ' . $httpCode);
        error_log('[Kravyo][RazorpayService] Refund FAILED for payment ' . $razorpayPaymentId . ': ' . $errorDesc . ' | Response: ' . $response);

        return ['success' => false, 'refund_id' => null, 'error' => $errorDesc];
    }
}

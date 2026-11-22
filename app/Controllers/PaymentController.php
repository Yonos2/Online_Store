<?php

namespace App\Controllers;

use App\Models\PaymentOrder;
use Database;
use JsonException;
use PayMongo;
use RuntimeException;
use Throwable;

final class PaymentController
{
    public function returnFromProvider(): void
    {
        session_start();
        if (
            !isset($_SESSION['user_id']) ||
            ($_SESSION['role'] ?? '') !== 'customer'
        ) {
            header('Location: ' . route_url('login'));
            exit;
        }

        $orderNumber = $_GET['order'] ?? '';
        if (
            !is_string($orderNumber) ||
            !preg_match('/^ORD-[A-Z0-9-]{1,60}$/', $orderNumber)
        ) {
            http_response_code(400);
            exit('Invalid order reference.');
        }

        $orders = new PaymentOrder((new Database())->connect());
        $order = $orders->forCustomerByNumber(
            $orderNumber,
            (int) $_SESSION['user_id']
        );
        if (!$order || $order['payment_method'] !== 'paymongo') {
            http_response_code(404);
            exit('Online payment order not found.');
        }

        $paymentResult = 'pending';
        try {
            $paymongo = new PayMongo();
            $session = $paymongo->retrieveCheckoutSession(
                (string) $order['paymongo_checkout_session_id']
            );

            if (PayMongo::isPaid($session)) {
                $orders->markPaid(
                    (int) $order['id'],
                    PayMongo::paymentId($session)
                );
                $paymentResult = 'paid';
                unset($_SESSION['paymongo_retry_url']);
            } else {
                $_SESSION['paymongo_retry_url'] = PayMongo::checkoutUrl($session);
                if (($_GET['result'] ?? '') === 'cancelled') {
                    $paymentResult = 'cancelled';
                }
            }
        } catch (Throwable $ex) {
            error_log('PayMongo return verification error: ' . $ex->getMessage());
            $_SESSION['paymongo_retry_url'] = null;
            $paymentResult = 'unverified';
        }

        $_SESSION['last_order_number'] = $orderNumber;
        header(
            'Location: ' . route_url(
                'orders/success?payment=' . rawurlencode($paymentResult)
            )
        );
        exit;
    }

    public function webhook(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed.']);
            exit;
        }

        $webhookSecret = getenv('PAYMONGO_WEBHOOK_SECRET');
        if (!is_string($webhookSecret) || $webhookSecret === '') {
            http_response_code(503);
            echo json_encode(['error' => 'Webhook is not configured.']);
            exit;
        }

        $rawBody = file_get_contents('php://input');
        $signatureHeader = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';
        if (!is_string($rawBody) || !is_string($signatureHeader)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid webhook request.']);
            exit;
        }

        $signatureParts = [];
        foreach (explode(',', $signatureHeader) as $part) {
            $pair = explode('=', trim($part), 2);
            if (count($pair) === 2) {
                $signatureParts[$pair[0]] = $pair[1];
            }
        }

        $timestamp = $signatureParts['t'] ?? '';
        $receivedSignature = $signatureParts['te'] ?? '';
        $expectedSignature = hash_hmac(
            'sha256',
            $timestamp . '.' . $rawBody,
            $webhookSecret
        );
        if (
            $timestamp === '' ||
            $receivedSignature === '' ||
            !hash_equals($expectedSignature, $receivedSignature)
        ) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid webhook signature.']);
            exit;
        }

        try {
            $event = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid webhook payload.']);
            exit;
        }

        if (
            ($event['data']['attributes']['type'] ?? '')
            !== 'checkout_session.payment.paid'
        ) {
            http_response_code(200);
            echo json_encode(['received' => true]);
            exit;
        }

        $checkoutSessionId = $this->findCheckoutSessionId($event);
        if ($checkoutSessionId === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Checkout session reference is missing.']);
            exit;
        }

        try {
            $orders = new PaymentOrder((new Database())->connect());
            $order = $orders->byCheckoutSession($checkoutSessionId);
            if (!$order) {
                http_response_code(200);
                echo json_encode(['received' => true]);
                exit;
            }

            $session = (new PayMongo())
                ->retrieveCheckoutSession($checkoutSessionId);
            if (!PayMongo::isPaid($session)) {
                throw new RuntimeException(
                    'PayMongo has not confirmed this payment yet.'
                );
            }

            $orders->markPaid(
                (int) $order['id'],
                PayMongo::paymentId($session)
            );
            http_response_code(200);
            echo json_encode(['received' => true]);
        } catch (Throwable $ex) {
            error_log('PayMongo webhook processing error: ' . $ex->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Unable to process payment notification.'
            ]);
        }
    }

    private function findCheckoutSessionId(mixed $value): ?string
    {
        if (!is_array($value)) {
            return is_string($value) && preg_match('/^cs_[A-Za-z0-9]+$/', $value)
                ? $value
                : null;
        }

        foreach ($value as $nestedValue) {
            $sessionId = $this->findCheckoutSessionId($nestedValue);
            if ($sessionId !== null) {
                return $sessionId;
            }
        }

        return null;
    }
}

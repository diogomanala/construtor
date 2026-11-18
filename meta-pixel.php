<?php
declare(strict_types=1);
/** Meta Pixel + CAPI do Kit RDC. Não contém credenciais. */
const OD_META_PIXEL_ID = '1426979209595089';

function od_meta_purchase_id(string $id): string {
    return 'od_purchase_'.substr(hash('sha256', $id), 0, 32);
}

/** Insere o pixel apenas nas páginas comerciais e no retorno de uma compra confirmada. */
function od_meta_pixel_head(string $page): string {
    $event = '';
    $amount = 67.00;
    $eventId = '';
    $purchase = $GLOBALS['od_meta_verified_order'] ?? null;
    if (!is_array($purchase)) {
        $purchase = $_SESSION['od_meta_pending_purchase'] ?? null;
    }
    if (is_array($purchase) && !empty($purchase['id']) && !empty($purchase['amount_cents'])) {
        $event = 'Purchase';
        $amount = (int)$purchase['amount_cents'] / 100;
        $eventId = od_meta_purchase_id((string)$purchase['id']);
        unset($_SESSION['od_meta_pending_purchase']);
    } elseif ($page === 'inicio') {
        $event = 'ViewContent';
    } elseif ($page === 'checkout') {
        $event = 'InitiateCheckout';
    } elseif ($page !== 'pedido') {
        return '';
    }
    $extra = '';
    if ($event !== '') {
        $data = json_encode([
            'value' => $amount,
            'currency' => 'BRL',
            'content_type' => 'product',
            'content_ids' => ['kit-rdc']
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        if (!is_string($data)) return '';
        $extra = "fbq('track', '".$event."', ".$data;
        if ($event === 'Purchase') {
            $id = json_encode($eventId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            $extra .= ", {eventID: ".$id."}";
        }
        $extra .= ");";
    }
    // Trecho padrão da Meta, executado diretamente no HEAD. Sem script de controle de cookies.
    return '<!-- Meta Pixel Code -->'
        .'<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?'
        .'n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;'
        .'n.push=n;n.loaded=!0;n.version="2.0";n.queue=[];t=b.createElement(e);'
        .'t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)'
        .'}(window,document,"script","https://connect.facebook.net/en_US/fbevents.js");'
        ."fbq('init', '".OD_META_PIXEL_ID."');"
        ."fbq('track', 'PageView');"
        .$extra
        .'</script>'
        .'<noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id='
        .OD_META_PIXEL_ID.'&amp;ev=PageView&amp;noscript=1"></noscript>'
        .'<!-- End Meta Pixel Code -->';
}

/** Guarda somente informações necessárias à atribuição da compra na API de Conversões. */
function od_meta_capture_checkout(string $orderId): void {
    try {
        $db = od_orders_db();
        $db->exec('CREATE TABLE IF NOT EXISTS od_meta_checkout_tracking (
            order_id TEXT PRIMARY KEY,
            consent INTEGER NOT NULL DEFAULT 0,
            fbp TEXT DEFAULT \'\',
            fbc TEXT DEFAULT \'\',
            user_agent TEXT DEFAULT \'\',
            ip TEXT DEFAULT \'\',
            sending_at TEXT,
            sent_at TEXT
        )');
        $fbp = substr((string)($_COOKIE['_fbp'] ?? ''), 0, 200);
        $fbc = substr((string)($_COOKIE['_fbc'] ?? ''), 0, 200);
        if (!preg_match('/^fb\.[0-9]+\.[0-9]+\.[A-Za-z0-9._-]+$/', $fbp)) $fbp = '';
        if (!preg_match('/^fb\.[0-9]+\.[0-9]+\.[A-Za-z0-9._-]+$/', $fbc)) $fbc = '';
        $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 400);
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        if (!filter_var($ip, FILTER_VALIDATE_IP)) $ip = '';
        $stmt = $db->prepare('INSERT OR IGNORE INTO od_meta_checkout_tracking
            (order_id,consent,fbp,fbc,user_agent,ip) VALUES (?,1,?,?,?,?)');
        $stmt->execute([$orderId, $fbp, $fbc, $ua, $ip]);
    } catch (Throwable $e) {
        error_log('[Odonto Meta] Falha ao guardar consentimento do pedido');
    }
}

/** Uma compra real por ID, nunca por clique ou simples visita à página de retorno. */
function od_meta_capi_purchase(array $order): void {
    if (($order['status'] ?? '') !== 'paid' || empty($order['id'])) return;
    $token = defined('OD_META_CAPI_TOKEN') ? (string)constant('OD_META_CAPI_TOKEN') : (string)(getenv('OD_META_CAPI_TOKEN') ?: '');
    if ($token === '' || !function_exists('curl_init')) return;
    try {
        $db = od_orders_db();
        $db->exec('CREATE TABLE IF NOT EXISTS od_meta_checkout_tracking (
            order_id TEXT PRIMARY KEY, consent INTEGER NOT NULL DEFAULT 0,
            fbp TEXT DEFAULT \'\', fbc TEXT DEFAULT \'\', user_agent TEXT DEFAULT \'\',
            ip TEXT DEFAULT \'\', sending_at TEXT, sent_at TEXT
        )');
        $q = $db->prepare('SELECT * FROM od_meta_checkout_tracking WHERE order_id=? AND consent=1 AND sent_at IS NULL');
        $q->execute([(string)$order['id']]);
        $tracking = $q->fetch(PDO::FETCH_ASSOC);
        if (!$tracking || empty($tracking['user_agent'])) return;
        $lock = $db->prepare("UPDATE od_meta_checkout_tracking SET sending_at=CURRENT_TIMESTAMP
          WHERE order_id=? AND sent_at IS NULL AND (sending_at IS NULL OR sending_at < datetime('now','-5 minutes'))");
        $lock->execute([(string)$order['id']]);
        if ($lock->rowCount() !== 1) return;
        $user = [
            'em' => [hash('sha256', strtolower(trim((string)$order['buyer_email'])))],
            'client_user_agent' => (string)$tracking['user_agent']
        ];
        if ($tracking['ip'] !== '') $user['client_ip_address'] = $tracking['ip'];
        if ($tracking['fbp'] !== '') $user['fbp'] = $tracking['fbp'];
        if ($tracking['fbc'] !== '') $user['fbc'] = $tracking['fbc'];
        $body = json_encode(['data' => [[
            'event_name' => 'Purchase',
            'event_time' => time(),
            'event_id' => od_meta_purchase_id((string)$order['id']),
            'action_source' => 'website',
            'event_source_url' => 'https://odontonegocios.com/rdc/?page=pedido',
            'user_data' => $user,
            'custom_data' => [
                'currency' => 'BRL',
                'value' => ((int)$order['amount_cents']) / 100,
                'content_type' => 'product',
                'content_ids' => ['kit-rdc']
            ]
        ]]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($body)) throw new RuntimeException('Falha ao codificar evento');
        $ch = curl_init('https://graph.facebook.com/v25.0/'.OD_META_PIXEL_ID.'/events');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer '.$token, 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $parsed = is_string($response) ? json_decode($response, true) : null;
        if ($status >= 200 && $status < 300 && is_array($parsed) && (int)($parsed['events_received'] ?? 0) > 0) {
            $db->prepare('UPDATE od_meta_checkout_tracking SET sent_at=CURRENT_TIMESTAMP,sending_at=NULL WHERE order_id=?')
                ->execute([(string)$order['id']]);
        } else {
            $db->prepare('UPDATE od_meta_checkout_tracking SET sending_at=NULL WHERE order_id=?')
                ->execute([(string)$order['id']]);
            error_log('[Odonto Meta] CAPI indisponível HTTP '.$status);
        }
    } catch (Throwable $e) {
        error_log('[Odonto Meta] Falha isolada no envio de conversão');
    }
}

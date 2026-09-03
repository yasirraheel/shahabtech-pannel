<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WarzoneTelegramController extends Controller
{
    private $apiKey = 'WAR_LoV98CIYjX6S6N17Hvmc2c2K';
    private $baseUrl = 'https://api.warzoneshop.in/api/v1';

    private function makeApiRequest($endpoint, $method = 'GET', $data = [])
    {
        try {
            $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
            $request = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Accept'    => 'application/json',
            ])->timeout(15);

            if (strtoupper($method) === 'POST') {
                $response = $request->post($url, $data);
            } else {
                $response = $request->get($url, $data);
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Warzone API Error: ' . $e->getMessage());
            return ['error' => 'Failed to connect to Warzone API: ' . $e->getMessage()];
        }
    }

    public function index()
    {
        $pageTitle = 'Warzone Telegram';
        $account = $this->makeApiRequest('me');
        $stats = $this->makeApiRequest('stats');
        $autobuyState = \App\Http\Controllers\CronController::getAutoBuyState();
        return view('admin.warzone_telegram', compact('pageTitle', 'account', 'stats', 'autobuyState'));

    }

    public function action(Request $request)
    {
        $action = strtolower(trim($request->input('action', 'start')));
        $action = ltrim($action, '/'); // handle /start, /shop etc.

        switch ($action) {
            case 'start':
            case 'me':
            case 'menu':
                $account = $this->makeApiRequest('me');
                return response()->json([
                    'success' => true,
                    'type'    => 'menu',
                    'data'    => $account,
                    'html'    => view('admin.partials.warzone_menu_response', compact('account'))->render(),
                ]);

            case 'shop':
            case 'products':
                $productsData = $this->makeApiRequest('products');
                $services = $productsData['services'] ?? [];
                return response()->json([
                    'success' => true,
                    'type'    => 'shop',
                    'data'    => $services,
                    'html'    => view('admin.partials.warzone_shop_response', compact('services'))->render(),
                ]);

            case 'product_detail':
                $serviceId = $request->input('service_id');
                $productsData = $this->makeApiRequest('products');
                $services = $productsData['services'] ?? [];
                $service = collect($services)->firstWhere('service_id', $serviceId);
                
                if (!$service) {
                    return response()->json(['success' => false, 'message' => 'Product not found']);
                }

                return response()->json([
                    'success' => true,
                    'type'    => 'product_detail',
                    'data'    => $service,
                    'html'    => view('admin.partials.warzone_product_detail_response', compact('service'))->render(),
                ]);

            case 'place_order':
                $serviceId = $request->input('service_id');
                $quantity  = max(1, (int) $request->input('quantity', 1));
                
                $result = $this->makeApiRequest('order', 'POST', [
                    'service_id' => $serviceId,
                    'quantity'   => $quantity,
                ]);

                // Save delivered products / links to database
                if (!empty($result['delivered_products']) && is_array($result['delivered_products'])) {
                    $serviceName = $result['service'] ?? ($result['name'] ?? 'Warzone Product');
                    foreach ($result['delivered_products'] as $item) {
                        try {
                            \App\Models\WarzonePurchasedLink::create([
                                'product_name' => $serviceName,
                                'service_id'   => $serviceId,
                                'order_id'     => $result['order_id'] ?? null,
                                'link'         => trim($item),
                                'source'       => 'bot',
                                'status'       => \App\Models\WarzonePurchasedLink::STATUS_AVAILABLE,
                                'purchased_at' => now(),
                            ]);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to store purchased link: ' . $e->getMessage());
                        }
                    }
                }

                // Re-fetch updated account info
                $account = $this->makeApiRequest('me');

                return response()->json([
                    'success' => isset($result['order_id']) || isset($result['success']),
                    'result'  => $result,
                    'account' => $account,
                    'html'    => view('admin.partials.warzone_order_result_response', compact('result', 'account'))->render(),
                ]);


            case 'orders':
            case 'order_history':
            case 'recover_order':
                $ordersData = $this->makeApiRequest('orders');
                $orders = $ordersData['orders'] ?? [];
                $counts = $ordersData['counts'] ?? [];
                return response()->json([
                    'success' => true,
                    'type'    => 'orders',
                    'data'    => $ordersData,
                    'html'    => view('admin.partials.warzone_orders_response', compact('orders', 'counts'))->render(),
                ]);

            case 'order_detail':
                $orderId = $request->input('order_id');
                $orderData = $this->makeApiRequest('order/' . $orderId);
                $order = $orderData['order'] ?? null;
                return response()->json([
                    'success' => (bool)$order,
                    'type'    => 'order_detail',
                    'data'    => $order,
                    'html'    => view('admin.partials.warzone_order_detail_response', compact('order'))->render(),
                ]);

            case 'wallet':
            case 'balance':
                $account = $this->makeApiRequest('me');
                $stats = $this->makeApiRequest('stats');
                return response()->json([
                    'success' => true,
                    'type'    => 'wallet',
                    'html'    => view('admin.partials.warzone_wallet_response', compact('account', 'stats'))->render(),
                ]);

            case 'profile':
                $account = $this->makeApiRequest('me');
                $stats = $this->makeApiRequest('stats');
                return response()->json([
                    'success' => true,
                    'type'    => 'profile',
                    'html'    => view('admin.partials.warzone_profile_response', compact('account', 'stats'))->render(),
                ]);

            case 'api_key':
            case 'apikey':
                $apiKey = $this->apiKey;
                return response()->json([
                    'success' => true,
                    'type'    => 'api_key',
                    'html'    => view('admin.partials.warzone_apikey_response', compact('apiKey'))->render(),
                ]);

            case 'stats':
                $stats = $this->makeApiRequest('stats');
                return response()->json([
                    'success' => true,
                    'type'    => 'stats',
                    'html'    => view('admin.partials.warzone_stats_response', compact('stats'))->render(),
                ]);

            case 'support':
                return response()->json([
                    'success' => true,
                    'type'    => 'support',
                    'html'    => view('admin.partials.warzone_support_response')->render(),
                ]);

            case 'autobuy':
            case 'sniper':
            case 'monitor':
                $autobuyState = \App\Http\Controllers\CronController::getAutoBuyState();
                $account = $this->makeApiRequest('me');
                return response()->json([
                    'success' => true,
                    'type'    => 'autobuy',
                    'data'    => $autobuyState,
                    'account' => $account,
                    'html'    => view('admin.partials.warzone_autobuy_response', compact('autobuyState', 'account'))->render(),
                ]);

            case 'check_now':
                $cronController = new \App\Http\Controllers\CronController();
                $cronController->warzoneAutoBuyGemini();
                $autobuyState = \App\Http\Controllers\CronController::getAutoBuyState();
                $account = $this->makeApiRequest('me');
                return response()->json([
                    'success' => true,
                    'type'    => 'autobuy',
                    'data'    => $autobuyState,
                    'account' => $account,
                    'html'    => view('admin.partials.warzone_autobuy_response', compact('autobuyState', 'account'))->render(),
                ]);


            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Unknown command: ' . $action,
                ]);
        }
    }
}

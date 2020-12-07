<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use function redirect;

class ShopifyController extends Controller
{
    public function auth(Request $request)
    {
        if (!$request->has(['shop', 'timestamp', 'hmac'])) {
            return 'パラメータが不正です';
        }

        $scope = 'read_orders';
        $redirectUri = 'https://ab8c0a13fc81.ngrok.io/api/shopify/token';
        $shop = $request->get('shop');
        $url = "https://{$shop}/admin/oauth/authorize?".http_build_query([
                'client_id' => env('SHOPIFY_API_KEY'),
                'scope' => $scope,
                'redirect_uri' => $redirectUri,
                'state' => bin2hex(random_bytes(16)),
            ]);

        if (!$this->verifyHmac($request->all())) {
            throw new InvalidArgumentException('パラメータが不正です');
        }


        return redirect($url);
    }

    public function token(Request $request)
    {
        [
            'code' => $authCode,
            'hmac' => $hmac,
            'shop' => $shop,
            'state' => $state,
            'timestamp' => $timestamp,
        ] = $request->all();

        if (!preg_match('/[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com[\/]?/', $shop)) {
            throw new InvalidArgumentException('hostnameが不正です');
        }
        if (!$this->verifyHmac($request->all())) {
            throw new InvalidArgumentException('パラメータが不正です');
        }

        $response = Http::post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => env('SHOPIFY_API_KEY'),
            'client_secret' => env('SHOPIFY_API_SECRET'),
            'code' => $authCode,
        ]);

        // responseからaccess tokenを保存する
    }

    private function verifyHmac(array $params): bool
    {
        // キーの辞書順にソートしておきます
        $parameters = collect($params)->sortBy(function ($_, $key) {
            return $key;
        });

        $hmac = $parameters->pull('hmac');
        if (!$hmac) {
            return false;
        }
        // hmacを除いたクエリ文字列
        // 例： shop=macaron-apptest.myshopify.com&timestamp=1607315468
        $queryString = http_build_query($parameters->toArray());

        $hash = hash_hmac(
            'sha256',
            $queryString,
            env('SHOPIFY_API_SECRET')
        );

        return $hash === $hmac;
    }
}

// {"access_token":"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","scope":"read_orders"}

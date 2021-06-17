<?php

namespace App\Services;

use Illuminate\Support\Arr;
use GuzzleHttp\Client;

class BotRate
{
    protected $url = 'https://rate.bot.com.tw/xrt/flcsv/0/day';
    const COLUMN_MAPPING = [
        'currency' => 0,
        'cash_buying' => 2,
        'cash_selling' => 12,
        'spot_buying' => 3,
        'spot_selling' => 13
    ];
    const CURRENCY_NAMES = [
        'USD' => '美元',
        'HKD' => '港幣',
        'GBP' => '英鎊',
        'AUD' => '澳幣',
        'CAD' => '加拿大幣',
        'SGD' => '新加坡幣',
        'CHF' => '瑞士法郎',
        'JPY' => '日圓',
        'ZAR' => '南非幣',
        'SEK' => '瑞典克朗',
        'NZD' => '紐西蘭幣',
        'THB' => '泰銖',
        'PHP' => '菲律賓披索',
        'IDR' => '印尼盾',
        'EUR' => '歐元',
        'KRW' => '菲律賓披索',
        'VND' => '越南幣',
        'MYR' => '馬來西亞幣',
        'CNY' => '人民幣',
    ];

    /**
     * handler
     *
     * @return array
     */
    public function handle()
    {
        ['rates' => $rates, 'updatedAt' => $updatedAt] = $this->fetchResource();

        return [
            'rates' => $rates,
            'created_at' => time(),
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * fetch csv resource and convert to data
     *
     * @return array
     */
    protected function fetchResource()
    {
        ['content' => $content, 'headers' => $headers] = $this->fetch();

        $updatedAt = $this->parseHeaderUpdateTime($headers);
        $rates = $this->parseContents($content);

        return compact('updatedAt', 'rates');
    }

    /**
     * emit request to retrive csv content and response headers
     *
     * @return array
     */
    protected function fetch()
    {
        $headers = [
            'Accept-language' => 'en',
            'Host' => 'rate.bot.com.tw',
        ];

        $client = new Client;
        $response = $client->request('GET', $this->url);

        $content = $response->getBody()->getContents();
        $headers = $response->getHeaders();

        return compact('content', 'headers');
    }

    /**
     * parse headers to get update time from attachemnt filename
     *
     * @param array $headers
     * @return string
     */
    protected function parseHeaderUpdateTime(array $headers)
    {
        if (!Arr::has($headers, 'Content-Disposition.0')) {
            return null;
        }

        $contentDisposition = Arr::get($headers, 'Content-Disposition.0');
        preg_match('/ExchangeRate@(.*).csv/', $contentDisposition, $matches);
        return isset($matches[1]) ? strtotime($matches[1]) : null;
    }

    /**
     * parse csv content
     *
     * @param string $content
     * @return array
     */
    protected function parseContents(string $content)
    {
        return collect(explode("\r\n", $content))
            ->slice(1)   // remove header
            ->map(function ($row) {
                return explode(',', $row);
            })
            ->filter(function ($values) {
                return count($values) >= 13;
            })
            ->map(function ($values) {
                $rates = collect(self::COLUMN_MAPPING)->mapWithKeys(function ($colIndex, $name) use ($values) {
                    $value = trim($values[$colIndex]) ?? '';
                    if (is_numeric($value)) {
                        $value = floatval($value);
                    }
                    return [$name => $value];
                });
                $rates['chinese_name'] = Arr::get(self::CURRENCY_NAMES, $rates['currency']);

                return $rates;
            })
            ->values()
            ->toArray();
    }
}

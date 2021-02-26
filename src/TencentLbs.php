<?php

namespace Qihucms\TencentLbs;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class TencentLbs
{
    /**
     * 格式化请求参数生成签名
     *
     * @param string $path 请求路径
     * @param array $query 请求参数
     * @param string $method 请求参数
     * @return array
     */
    protected function formatQueryString($path, $query, $method)
    {
        $query = array_merge(['key' => config('qihu_lbs.tencent_lbs_key')], $query);
        if ($method !== 'GET' && is_array($query['data'])) {
            $query['data'] = json_encode($query['data']);
            $query['data'] = '[' . $query['data'] . ']';
        }
        $query = Arr::sortRecursive($query);

        if (config('qihu_lbs.tencent_lbs_sk')) {
            $sign = md5($path . '?' . urldecode(Arr::query($query)) . config('qihu_lbs.tencent_lbs_sk'));
            $query = array_merge(['sig' => $sign], $query);
        }

        return $query;
    }

    /**
     * 发送请求
     *
     * @param string $path
     * @param array $query
     * @param string $method
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request($path = '', $query = [], $method = 'GET')
    {
        $client = new Client(['base_uri' => 'https://apis.map.qq.com', 'timeout' => 30.0, 'verify' => false]);

        $queryString = $this->formatQueryString($path, $query, $method);
        if ($method === 'GET') {
            $query = ['query' => $queryString];
        } else {
            $path .= '?sig=' . $queryString['sig'];
            unset($queryString['sig']);
            $queryString['data'] = $query['data'];
            $query = ['json' => $queryString];
        }

        $response = $client->request($method, $path, $query);
        return json_decode((string)$response->getBody(), true);
    }

    /**
     * IP定位
     *
     * @param $ip
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function ipLocation($ip)
    {
        if (false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            $result = $this->request('/ws/location/v1/ip', ['ip' => $ip]);
            if ($result['status'] == 0) {
                // 国外IP时，详细信息无法通过IP获取到，根据返回的GPS信息，重新获取
                if ($result['result']['ad_info']['adcode'] < 1) {
                    $gpsData = $this->gpsLocation($result['result']['location']['lat'], $result['result']['location']['lng']);
                    $result['result']['ad_info']['province'] = $gpsData['result']['address_component']['ad_level_1'];
                    $result['result']['ad_info']['city'] = $gpsData['result']['address_component']['ad_level_2'];
                    $result['result']['ad_info']['district'] = $gpsData['result']['address_component']['ad_level_3'];
                }
                return $result;
            }
        }
        return [
            'status' => 0,
            'message' => '局域网IP',
            'result' => [
                'ip' => $ip,
                'location' => [
                    "lng" => 0,
                    "lat" => 0
                ],
                'ad_info' => [
                    'nation' => '中国',
                    'province' => '本地',
                    'city' => '局域网',
                    'district' => '',
                    'adcode' => '000000'
                ]
            ]
        ];
    }

    /**
     * 经纬度逆地址解析（坐标位置描述）
     *
     * @param string $latitude
     * @param string $longitude
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function gpsLocation($latitude = '0', $longitude = '0')
    {
        return $this->request('/ws/geocoder/v1', ['location' => $latitude . ',' . $longitude]);
    }
}
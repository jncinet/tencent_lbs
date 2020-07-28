<?php

namespace Qihucms\TencentLbs;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class TencentLbs
{
    /**
     * 格式化请求参数生成签名
     *
     * @param string $path 请求路径
     * @param array $query 请求参数
     * @return array
     */
    protected function formatQueryString($path, $query)
    {
        $query = array_merge(['key' => Cache::get('config_map_tencent_lbs_key', '')], $query);
        sort($query);

        $sign = md5($path . '?' . http_build_query($query) . Cache::get('config_map_tencent_lbs_sk', ''));
        return array_merge(['sig' => $sign], $query);
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
        $query = $this->formatQueryString($path, $query);

        if ($method === 'GET') {
            $query = ['query' => $query];
        } else {
            $query = ['body' => $query];
        }

        $client = new Client(['base_uri' => 'https://apis.map.qq.com', 'verify' => false]);
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
        return $this->request('/ws/location/v1/ip', ['ip' => $ip]);
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
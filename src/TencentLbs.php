<?php

namespace Qihucms\TencentLbs;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class TencentLbs
{
    protected function get($url = '', $query = [])
    {
        $client = new Client(['verify' => false]);
        $response = $client->request(
            'GET',
            $url,
            [
                'query' => $query
            ]);
        return json_decode((string)$response->getBody(), true);
    }

    /**
     * IP定位
     *
     * @param $ip
     * @return mixed
     */
    public function ipLocation($ip)
    {
        return $this->get(
            'https://apis.map.qq.com/ws/location/v1/ip',
            [
                'ip' => $ip,
                'key' => Cache::get('config_map_tencent_lbs_key', '')
            ]
        );
    }

    /**
     * 经纬度逆地址解析（坐标位置描述）
     *
     * @param string $latitude
     * @param string $longitude
     * @return mixed
     */
    public function gpsLocation($latitude = '0', $longitude = '0')
    {
        return $this->get(
            'https://apis.map.qq.com/ws/geocoder/v1/',
            [
                'location' => $latitude . ',' . $longitude,
                'key' => Cache::get('config_map_tencent_lbs_key', '')
            ]
        );
    }
}
<?php

namespace CryptoUnifier\JetstreamPlus;

use InvalidArgumentException;

use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Support\Arrayable;

use Illuminate\Database\Eloquent\Model;

class IpAddress extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ip_address';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip_address',
        'asn',
        'continent',
        'country',
        'country_code',
        'region',
        'region_code',
        'city',
        'latitude',
        'longitude',
        'risk',
        'proxy',
        'driver',
    ];

    /**
     * Create a new element.
     *
     * @param mixed $id
     * @param mixed $columns
     *
     * @return self
     */
    public static function find($id, $columns = ['*'])
    {
        $config = config('ip_address');

        if (is_array($id) || $id instanceof Arrayable) {
            throw new InvalidArgumentException('IpAddress::find() expects a string as the first parameter.');
        }

        $result = self::whereKey($id)->first($columns);

        if ($result) {
            if ($result->updated_at < now()->subSeconds($config['data_duration'])) {
                return self::updateOrCreateIpAddress($id);
            }

            return $result;
        }

        return self::updateOrCreateIpAddress($id);
    }

    /**
     * Create a new element with current user ip address.
     *
     * @return self
     */
    public static function currentUser()
    {
        return self::find(optional(request())->ip());
    }

    /**
     * Get location string attribute.
     *
     * @return string|null
     */
    public function getLocationAttribute()
    {
        if (! $this->country) {
            return;
        }

        return trim("{$this->country}, {$this->city} - {$this->region}");
    }

    /**
     * Update or create IP address.
     */
    protected static function updateOrCreateIpAddress(string $ipAddress)
    {
        $config = config('ip_address');

        if ($config['key']) {
            if ($config['driver'] === 'proxycheck') {
                $result = self::proxyCheckRequest($ipAddress, $config);
            } else {
                throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
            }
        } else {
            $result = false;
        }

        if (! $result) {
            return new self(['ip_address' => $ipAddress]);
        }

        // Ensure that it will not try to create with an existing row

        return self::updateOrCreate(['ip_address' => $ipAddress], $result);
    }

    /**
     * Make request to proxyCheck service.
     *
     * @return array|null
     */
    protected static function proxyCheckRequest(string $ip, array $config)
    {
        $response = Http::timeout(5)->get("https://proxycheck.io/v2/{$ip}?key={$config['key']}&vpn=1&asn=1&risk=1")->json();

        if (! isset($response['status']) || $response['status'] !== 'ok') {
            return;
        }

        $response = $response[$ip];

        return [
            'ip_address' => $ip,
            'asn' => $response['asn'] ?? 'Unknown',
            'continent' => $response['continent'],
            'country' => $response['country'],
            'country_code' => $response['isocode'],
            'region' => $response['region'] ?? 'Unknown',
            'region_code' => $response['regioncode'] ?? '00',
            'city' => $response['city'] ?? 'Unknown',
            'latitude' => $response['latitude'],
            'longitude' => $response['longitude'],
            'risk' => $response['risk'],
            'proxy' => ($response['proxy'] === 'yes') ? 1 : 0,
            'driver' => 'proxycheck',
        ];
    }
}

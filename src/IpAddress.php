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
     * Create a new element with current request ip address.
     */
    public static function currentRequest(): self
    {
        return self::find(optional(request())->ip());
    }

    /**
     * Get location string attribute.
     */
    public function getLocationAttribute(): ?string
    {
        if (! $this->country) {
            return null;
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
            } elseif ($config['driver'] === 'ipregistry') {
                $result = self::ipRegistryRequest($ipAddress, $config);
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
     */
    protected static function proxyCheckRequest(string $ip, array $config): ?array
    {
        $response = Http::timeout(5)->get("https://proxycheck.io/v2/{$ip}?key={$config['key']}&vpn=1&asn=1&risk=1")->json();

        if (! isset($response[$ip])) { // Cannot check status === 'ok', since status is not always ok, even when the request is successful
            return null;
        }

        $response = $response[$ip];

        return [
            'ip_address' => $ip,
            'asn' => $response['asn'] ?? 'Unknown',
            'continent' => $response['continent'],
            'country' => $response['country'] ?? 'Unknown',
            'country_code' => $response['isocode'] ?? 'XX',
            'region' => $response['region'] ?? 'Unknown',
            'region_code' => $response['regioncode'] ?? '00',
            'city' => $response['city'] ?? 'Unknown',
            'latitude' => $response['latitude'] ?? 0,
            'longitude' => $response['longitude'] ?? 0,
            'risk' => (int) ($response['risk'] ?? '100'),
            'proxy' => ($response['proxy'] === 'yes') ? 1 : 0,
            'driver' => 'proxycheck',
        ];
    }

    /**
     * Make request to ipRegistry service.
     */
    protected static function ipRegistryRequest(string $ip, array $config): ?array
    {
        $response = Http::timeout(5)->get("https://api.ipregistry.co/{$ip}?key={$config['key']}")->json();

        if (isset($response['code']) || ! isset($response['ip'])) {
            return null;
        }

        $isProxy = false;
        foreach ($response['security'] as $type => $value) {
            if ($value) {
                $isProxy = true;
                break;
            }
        }

        return [
            'ip_address' => $ip,
            'asn' => 'AS' . ((string) $response['connection']['asn']),
            'continent' => $response['location']['continent']['continent'],
            'country' => $response['location']['country']['name'],
            'country_code' => $response['location']['country']['code'],
            'region' => $response['location']['region']['name'],
            'region_code' => $response['location']['region']['code'],
            'city' => $response['location']['city'],
            'latitude' => $response['location']['latitude'],
            'longitude' => $response['location']['longitude'],
            'risk' => ($isProxy) ? 100 : 0,
            'proxy' => (int) $isProxy,
            'driver' => 'ipregistry',
        ];
    }
}

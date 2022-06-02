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
    public static function find($id, $columns = ['*'], bool $update = false)
    {
        $config = config('ip_address');

        /*
         * Get result if no update mandatory
         * Validate `update_at` ip address data
         */
        if (! $update) {
            $result = (is_array($id) || $id instanceof Arrayable)
                ? self::findMany($id, $columns)
                : self::whereKey($id)->first($columns);

            if ($result) {
                if ($result->update_at < now()->subSeconds($config['data_duration'])) {
                    return self::find($id, $columns, true);
                }

                return $result;
            }
        }

        /*
         * No information cached on database, request to driver.
         */
        if ($config['key']) {
            if ($config['driver'] === 'proxycheck') {
                $result = self::proxyCheckRequest($id, $config);
            } else {
                throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
            }
        } else {
            $result = false;
        }

        /*
         * Driver returned an invalid response.
         */
        if (! $result) {
            return new self(['ip_address' => $id]);
        }

        return (! $update)
            ? self::create($result)
            : self::where('ip_address', $id)->update($result);
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
     * Make request to proxyCheck service.
     *
     * @return array|null
     */
    protected static function proxyCheckRequest(string $ip, array $config)
    {
        $response = Http::get("https://proxycheck.io/v2/{$ip}?key={$config['key']}&vpn=1&asn=1")->json();

        if (! isset($response['status']) || $response['status'] !== 'ok') {
            return;
        }

        $response = $response[$ip];

        return [
            'ip_address' => $ip,
            'asn' => $response['asn'],
            'continent' => $response['continent'],
            'country' => $response['country'],
            'country_code' => $response['country_code'],
            'region' => $response['region'],
            'region_code' => $response['region_code'],
            'city' => $response['city'],
            'latitude' => $response['latitude'],
            'longitude' => $response['longitude'],
            'risk' => $response['risk'],
            'proxy' => $response['proxy'],
            'driver' => 'proxycheck',
        ];
    }
}

<?php


namespace App\Library\Services;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class Meteobot
 *
 * @package App\Library\Services
 */
class Meteobot
{
    /**
     * @var Client
     */
    public Client $client;

    /**
     * Формат времени по умолчанию
     *
     * @var string
     */
    private string $timeFormat = 'iso-8601';

    /**
     * Часовой пояс по умолчанию
     *
     * @var string
     */
    private string $timeZone = 'UTC';

    /**
     * Meteobot constructor.
     *
     * @param  string  $username
     * @param  string  $password
     */
    public function __construct (string $username, string $password)
    {
        $this->client = new Client([
            'base_uri' => config('meteobot.api_url'),
            'auth' => [$username, $password]
        ]);
    }

    /**
     * Получить агрегированные по часам данные за указанный период
     *
     * @param  string  $id
     * @param  string  $startDate
     * @param  string  $endDate
     *
     * @return Collection
     * @throws GuzzleException
     */
    public function index (string $id, string $startDate, string $endDate): Collection
    {
        return $this->csv2collect($this->client->get('Index', [
            'query' => [
                'id' => $id,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'timeFormat' => $this->timeFormat,
                'timeZone' => $this->timeZone,
            ]
        ])->getBody()->getContents());
    }

    /**
     * Получить не агрегированные данные за указанный период
     *
     * @param  string  $id
     * @param  string  $startTime
     * @param  string  $endTime
     *
     * @return Collection
     * @throws GuzzleException
     */
    public function indexFull (string $id, string $startTime, string $endTime): Collection
    {
        return $this->csv2collect($this->client->get('IndexFull', [
            'query' => [
                'id' => $id,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'timeFormat' => $this->timeFormat,
                'timeZone' => $this->timeZone,
            ]
        ])->getBody()->getContents());
    }

    /**
     * Получить текущее местоположение
     *
     * @param  string  $id
     *
     * @return Collection
     * @throws GuzzleException
     */
    public function locate (string $id): Collection
    {
        return $this->csv2collect($this->client->get('Locate', [
            'query' => [
                'id' => $id,
                'timeFormat' => $this->timeFormat,
                'timeZone' => $this->timeZone,
            ]
        ])->getBody()->getContents());
    }

    /**
     * Преобразовать строку CSV в коллекцию
     *
     * @param  string  $csv
     *
     * @return Collection
     */
    private function csv2collect (string $csv): Collection
    {
        $result = collect();
        $rows   = [];

        foreach (explode("\n", $csv) as $row)
            $rows[] = str_getcsv($row, ';');

        $headers = Arr::first($rows);
        $body = Arr::except($rows, 0);

        foreach ($body as $row)
            if (count($headers) === count($row))
                $result->push($this->parseData((array) array_combine($headers, $row)));

        return $result;
    }

    /**
     * Обработать данные
     *
     * @param  array  $data
     *
     * @return array
     */
    private function parseData (array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $result[$key] = match ($key) {
                'id', 'name' => $value,
                'time' => Carbon::parse($data['time'], $this->timeZone)->timezone(config('app.timezone')),
                default => (float) $value,
            };
        }

        return $result;
    }
}

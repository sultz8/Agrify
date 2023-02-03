<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\FuelLevelSensorRequest;
use App\Models\FuelLevelSensor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class FuelLevelSensorAPIController
 *
 * @package App\Http\Controllers\API\V1
 */
class FuelLevelSensorAPIController extends APIController
{
    /**
     * FuelLevelSensorAPIController constructor.
     */
    public function __construct()
    {
        $this->authorizeResource(FuelLevelSensor::class);
    }

    /**
     * Получить список датчиков уровня топлива
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->sendSuccess(__('rest.index_success'), FuelLevelSensor::all());
    }

    /**
     * Создать новый датчик уровня топлива
     *
     * @param  FuelLevelSensorRequest  $request
     *
     * @return JsonResponse
     */
    public function store(FuelLevelSensorRequest $request): JsonResponse
    {
        $fuelLevelSensor = new FuelLevelSensor($request->validated());
        $fuelLevelSensor->organization()->associate($request->user()->currentAccessToken()->organization_id);

        $fuelLevelSensor->save();

        return $this->sendSuccess(__('rest.store_success'), $fuelLevelSensor, Response::HTTP_CREATED);
    }

    /**
     * Получить указанный датчик уровня топлива
     *
     * @param  FuelLevelSensor  $fuelLevelSensor
     *
     * @return JsonResponse
     */
    public function show(FuelLevelSensor $fuelLevelSensor): JsonResponse
    {
        return $this->sendSuccess(__('rest.show_success'), $fuelLevelSensor);
    }

    /**
     * Обновить указанный датчик уровня топлива
     *
     * @param  FuelLevelSensorRequest  $request
     * @param  FuelLevelSensor  $fuelLevelSensor
     *
     * @return JsonResponse
     */
    public function update(FuelLevelSensorRequest $request, FuelLevelSensor $fuelLevelSensor): JsonResponse
    {
        $fuelLevelSensor->update($request->validated());

        $fuelLevelSensor->save();

        return $this->sendSuccess(__('rest.update_success'), $fuelLevelSensor);
    }

    /**
     * Удалить указанный датчик уровня топлива
     *
     * @param  FuelLevelSensor  $fuelLevelSensor
     *
     * @return JsonResponse
     */
    public function destroy(FuelLevelSensor $fuelLevelSensor): JsonResponse
    {
        $fuelLevelSensor->delete();

        return $this->sendSuccess(__('rest.delete_success'));
    }
}

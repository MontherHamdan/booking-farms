<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use App\Http\Requests\CityRequest;

class CityController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;


    public function index()
    {
        /**
         * List all published cities ordered by order column.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            $cities = City::published()->ordered()->get();

            return $this->successResponse(true, $cities, null, 200);
        } catch (\Exception $e) {
            $this->logException($e);

            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }


    public function store(CityRequest $request)
    {
        /**
         * Store a new city with its image and order.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\JsonResponse
         */
        $request->validate([
            'name'   => 'required|string|max:255',
            'status' => ['required', Rule::in([City::STATUS_PUBLISHED, City::STATUS_UNPUBLISHED])],
            'image'  => 'required|image|max:2048',
            'order'  => 'required|integer|unique:cities,order',
        ]);

        try {
            $data = $request->only(['name', 'status', 'order']);

            if ($request->hasFile('image')) {
                // Generate human-readable filename
                $ext      = $request->file('image')->getClientOriginalExtension();
                $slug     = Str::slug($data['name']);
                $filename = "{$slug}-" . time() . ".{$ext}";

                // Upload to S3
                $path = $request->file('image')
                                ->storeAs('cities', $filename, 's3');

                $data['image'] = Storage::disk('s3')->url($path);
            }

            $city = City::create($data);

            return $this->successResponse(true, $city, null, 201);
        } catch (\Exception $e) {
            $this->logException($e, ['request' => $request->all()]);

            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }


    public function show(City $city)
    {
        /**
         * Show details of a single city.
         *
         * @param  \App\Models\City  $city
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            return $this->successResponse(true, $city, null, 200);
        } catch (\Exception $e) {
            $this->logException($e, ['city_id' => $city->id]);

            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }


    public function update(CityRequest $request, $city_id)
    {
        /**
         * Update a city's data and/or image.
         *
         * @param  \App\Http\Requests\CityRequest  $request
         * @param  int  $city_id
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            // Manually fetch the city
            $city = City::findOrFail($city_id);
            
            // Get the data from the request
            $data = $request->only(['name', 'status', 'order']);
    
            // Handle image upload if present
            if ($request->hasFile('image')) {
                if ($city->image) {
                    $oldPath = parse_url($city->image, PHP_URL_PATH);
                    if ($oldPath) {
                        $oldKey = ltrim($oldPath, '/');
                        Storage::disk('s3')->delete($oldKey);
                    }
                }
    
                // Generate new filename
                $nameForSlug = $data['name'] ?? $city->name;
                $ext = $request->file('image')->getClientOriginalExtension();
                $slug = Str::slug($nameForSlug);
                $filename = "{$slug}-" . time() . ".{$ext}";
    
                $path = $request->file('image')
                                ->storeAs('cities', $filename, 's3');
    
                $data['image'] = Storage::disk('s3')->url($path);
            }
    
            // Update the city
            $city->update($data);
            
            // Refresh to ensure we have the latest data
            $city->refresh();
    
            return $this->successResponse(true, $city, 'City updated successfully', 200);
        } catch (\Exception $e) {
            $this->logException($e, [
                'request' => $request->all(), 
                'city_id' => $city_id
            ]);
    
            return $this->errorResponse(__('error.internal_error') . ': ' . $e->getMessage(), 500);
        }
    }

    public function destroy($city_id)
    {
        /**
         * Delete a city (and its image) by ID.
         *
         * @param  int  $city_id
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            $city = City::findOrFail($city_id);
    
            if ($city->image) {
                $oldPath = parse_url($city->image, PHP_URL_PATH);
                if ($oldPath) {
                    $oldKey = ltrim($oldPath, '/');
                    Storage::disk('s3')->delete($oldKey);
                }
            }
    
            $city->delete();
    
            return $this->successResponse(true, 'City deleted successfully', null, 204);
        } catch (\Exception $e) {
            $this->logException($e, ['action' => 'delete category', 'category_id' => $id]);
            return $this->errorResponse(__('error.internal_error') . ': ' . $e->getMessage(), 500);
        }
    }
    
}
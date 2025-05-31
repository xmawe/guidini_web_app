<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTourRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Tour basic information
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'max_group_size' => 'required|integer|min:1|max:50',
            'city_id' => 'required|exists:cities,id',
            'availability_status' => 'nullable|in:available,unavailable,temporarily_unavailable',
            'is_transport_included' => 'nullable|boolean',
            'is_food_included' => 'nullable|boolean',

            // Activities validation
            'activities' => 'required|array|min:1',
            'activities.*.title' => 'required|string|max:255',
            'activities.*.description' => 'required|string|max:1000',
            'activities.*.duration' => 'required|integer|min:1',
            'activities.*.price' => 'nullable|numeric|min:0',
            'activities.*.activity_category_id' => 'required|exists:activity_categories,id',

            // Location validation for activities
            'activities.*.location' => 'required|array',
            'activities.*.location.longitude' => 'required|numeric|between:-180,180',
            'activities.*.location.latitude' => 'required|numeric|between:-90,90',
            'activities.*.location.label' => 'nullable|string|max:255',

            // Tour dates validation
            'tour_dates' => 'required|array|min:1',
            'tour_dates.*.day_of_week' => 'required_with:tour_dates|integer|between:0,6',
            'tour_dates.*.start_time' => 'required_with:tour_dates|date_format:H:i',
            'tour_dates.*.end_time' => 'required_with:tour_dates|date_format:H:i|after:tour_dates.*.start_time',

            // Images validation
            'images' => 'required|array|min:1|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Tour title is required.',
            'title.max' => 'Tour title cannot exceed 255 characters.',
            'description.required' => 'Tour description is required.',
            'description.max' => 'Tour description cannot exceed 2000 characters.',
            'price.required' => 'Tour price is required.',
            'price.numeric' => 'Tour price must be a valid number.',
            'price.min' => 'Tour price cannot be negative.',
            'duration.required' => 'Tour duration is required.',
            'duration.integer' => 'Tour duration must be a whole number.',
            'duration.min' => 'Tour duration must be at least 1 hour.',
            'max_group_size.required' => 'Maximum group size is required.',
            'max_group_size.integer' => 'Maximum group size must be a whole number.',
            'max_group_size.min' => 'Maximum group size must be at least 1 person.',
            'max_group_size.max' => 'Maximum group size cannot exceed 50 people.',
            'city_id.required' => 'Please select a city for the tour.',
            'city_id.exists' => 'Selected city does not exist.',
            'availability_status.in' => 'Invalid availability status.',

            // Activities messages
            'activities.required' => 'At least one activity is required for the tour.',
            'activities.array' => 'Activities must be provided as an array.',
            'activities.min' => 'At least one activity is required.',
            'activities.*.title.required' => 'Activity title is required.',
            'activities.*.title.max' => 'Activity title cannot exceed 255 characters.',
            'activities.*.description.required' => 'Activity description is required.',
            'activities.*.description.max' => 'Activity description cannot exceed 1000 characters.',
            'activities.*.duration.required' => 'Activity duration is required.',
            'activities.*.duration.integer' => 'Activity duration must be a whole number.',
            'activities.*.duration.min' => 'Activity duration must be at least 1 minute.',
            'activities.*.price.numeric' => 'Activity price must be a valid number.',
            'activities.*.price.min' => 'Activity price cannot be negative.',
            'activities.*.activity_category_id.required' => 'Activity category is required.',
            'activities.*.activity_category_id.exists' => 'Selected activity category does not exist.',

            // Location messages
            'activities.*.location.required' => 'Activity location is required.',
            'activities.*.location.longitude.required' => 'Longitude is required for activity location.',
            'activities.*.location.longitude.numeric' => 'Longitude must be a valid number.',
            'activities.*.location.longitude.between' => 'Longitude must be between -180 and 180.',
            'activities.*.location.latitude.required' => 'Latitude is required for activity location.',
            'activities.*.location.latitude.numeric' => 'Latitude must be a valid number.',
            'activities.*.location.latitude.between' => 'Latitude must be between -90 and 90.',
            'activities.*.location.label.max' => 'Location label cannot exceed 255 characters.',

            // Tour dates messages
            'tour_dates.required' => 'At least one tour date is required for the tour.',
            'tour_dates.min' => 'At least one tour date is required.',
            'tour_dates.*.day_of_week.required_with' => 'Day of week is required when tour dates are provided.',
            'tour_dates.*.day_of_week.integer' => 'Day of week must be a number.',
            'tour_dates.*.day_of_week.between' => 'Day of week must be between 0 (Sunday) and 6 (Saturday).',
            'tour_dates.*.start_time.required_with' => 'Start time is required when tour dates are provided.',
            'tour_dates.*.start_time.date_format' => 'Start time must be in HH:MM format.',
            'tour_dates.*.end_time.required_with' => 'End time is required when tour dates are provided.',
            'tour_dates.*.end_time.date_format' => 'End time must be in HH:MM format.',
            'tour_dates.*.end_time.after' => 'End time must be after start time.',

            // Images messages
            'images.required' => 'At least one image is required for the tour.',
            'images.min' => 'At least one image is required.',
            'images.max' => 'Maximum 5 images are allowed.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, JPG, or GIF format.',
            'images.*.max' => 'Each image cannot exceed 2MB.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'tour title',
            'description' => 'tour description',
            'price' => 'tour price',
            'duration' => 'tour duration',
            'max_group_size' => 'maximum group size',
            'city_id' => 'city',
            'availability_status' => 'availability status',
            'is_transport_included' => 'transport inclusion',
            'is_food_included' => 'food inclusion',
            'activities' => 'activities',
            'tour_dates' => 'tour dates',
            'images' => 'tour images',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation: Check if total activity duration doesn't exceed tour duration
            if ($this->has('activities') && $this->has('duration')) {
                $totalActivityDuration = collect($this->activities)->sum('duration');
                $tourDuration = $this->duration * 60; // Convert hours to minutes

                if ($totalActivityDuration > $tourDuration) {
                    $validator->errors()->add('activities',
                        'Total activity duration (' . $totalActivityDuration . ' minutes) cannot exceed tour duration (' . $tourDuration . ' minutes).'
                    );
                }
            }
        });
    }
}

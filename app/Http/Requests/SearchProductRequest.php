<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class SearchProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() :bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules() :array
    {
        return [
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate'
        ];
    }

    /**
     * adding startDate and endDate to inputs if those not set
     * @return void
     */
    public function prepareForValidation() :void
    {
        if(! $this->has('startDate'))
            $this->offsetSet('startDate', Carbon::today()->format('Y-m-d'));
        if(! $this->has('endDate'))
            $this->offsetSet('endDate', Carbon::today()->addWeeks(2)->format('Y-m-d'));
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules()
	{
		return [
			'title'       => 'required|string|max:50|min:1',
			'content'     => 'required|string|max:250|min:5',
			'num_of_days' => 'required|numeric|max:180|min:1',
			'min_price'   => 'required|numeric|max:8000|min:5',
			'max_price'   => 'required|numeric|max:10000|gt:min_price',
			'exp'         => ['required', 'string', Rule::in(['beginer', 'intermediate', 'experienced'])],
		];
	}
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DropzoneRequest extends FormRequest
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
			'image'           => 'nullable|image|mimes:jpg,gif,jpeg,webp|max:8000',
			'application'     => 'nullable|file|mimes:pdf,ppt,doc,xls|max:20000',
			'video'           => 'nullable|file|mimes:mp4,mov,flv,avi|max:100000',
			'dir'             => ['required', 'string', Rule::in(['projects/', 'messages/'])],
			'type'            => ['required', 'string', Rule::in(['image', 'video', 'application'])],
		];
	}
}

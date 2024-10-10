<?php

namespace App\Interfaces\Repository;

use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\StreamedResponse;

interface FileRepositoryInterface
{
	public function download_file(string $file, string $dir):StreamedResponse;
	public function insertFiles(Request $request, string $table_name, string $column_name, int $column_value):array;
	public function upload_file(Request $request):array;
	public function destroy_file(string $file, string $dir):JsonResponse;
}

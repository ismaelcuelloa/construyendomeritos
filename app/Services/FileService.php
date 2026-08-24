<?php

namespace App\Services;

use App\Models\File;
use App\Validators\Files\FilesValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileService extends BaseService
{
    /**
     * @throws ValidationException
     */
    public function create(array $data): File
    {
        FilesValidator::validate($data);

        try {
            $data['name'] = $data['name'] ?? Str::random(32);
            $data['path'] = $data['path'] ?? 'files';
            $file = new File;
            $file->fill($data);

            try {
                $dataFile = $this->createFile($data['file'], $file->path, $file->name);
                $file->name = $dataFile['name'];
            } catch (\Exception $e) {
                $this->error('No se pudo crear el archivo');
            }

            $file->save();

            return $file;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function createFile(UploadedFile $file, ?string $path = null, ?string $name = null): array
    {
        $name = $name ?? Str::random(32);
        $path = $path ?? 'files';
        try {
            $extension = $file->getClientOriginalExtension();
            $name = $name.'.'.$extension;
            $file_name = $path.$name;
            $file->move(public_path($path), $name);

            return ['path' => $path, 'name' => $name, 'file_name' => $file_name];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

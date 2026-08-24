<?php

namespace App\Services;

use App\Models\File;
use App\Models\ModuleFile;
use App\Validators\Courses\Modules\ModuleFiles\ModuleFilesValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ModuleFileService extends BaseService
{
    /**
     * @throws ValidationException
     */
    public function create(array $data): ModuleFile
    {

        ModuleFilesValidator::validate($data);

        try {
            $this->initTransactions();

            if (! isset($data['file_id']) || $data['file_id'] <= 0) {
                if (isset($data['file'])) {
                    try {
                        $data['file_id'] = $this->createFile($data['file'], $data['module_id'])->id;
                    } catch (\Exception $e) {
                        $this->error('Error al crear el archivo');
                    }
                } else {
                    $this->error(['file_id' => 'Archivo es requerido']);
                }
            }

            $moduleFile = new ModuleFile;
            $moduleFile->fill($data);
            $moduleFile->save();

            $this->commitTransactions();
        } catch (\Exception $e) {
            $this->rollbackTransactions();
            throw $e;
        }

        return $moduleFile;
    }

    /**
     * @throws ValidationException
     */
    public function update(string|int $id, array $data): ModuleFile
    {

        $data['create'] = false;
        $data['id'] = $id;
        ModuleFilesValidator::validate($data);

        try {
            $this->initTransactions();

            if (isset($data['file'])) {
                try {
                    $data['file_id'] = $this->createFile($data['file'], $data['module_id'])->id;
                } catch (\Exception $e) {
                    $this->error('Error al crear el archivo');
                }
            }

            $moduleFile = ModuleFile::find($id);
            $moduleFile->fill($data);
            $moduleFile->save();

            $this->commitTransactions();
        } catch (\Exception $e) {
            $this->rollbackTransactions();
            throw $e;
        }

        return $moduleFile;
    }

    public function createFile(UploadedFile $file, string|int $id): File
    {

        try {
            $f = new File;

            $randomString = Str::random(32);
            $extension = $file->getClientOriginalExtension();
            $f->name = $randomString.'.'.$extension;
            $f->path = 'files/modules/'.$id;
            $f->type = File::TYPE_DOCUMENT;

            $file->move(public_path($f->path), $f->name);
            $f->save();

            return $f;
        } catch (\Exception $e) {
            throw $e;
        }

    }

    public function delete(string|int $id): void
    {
        try {
            $moduleFile = ModuleFile::query()->findOrFail($id);
        } catch (\Exception $e) {
            $this->error('Archivo de módulo no encontrado');
        }

        try {
            $this->initTransactions();
            $moduleFile->delete();
            $this->commitTransactions();
        } catch (\Exception $e) {
            $this->rollbackTransactions();
            throw $e;
        }

    }
}

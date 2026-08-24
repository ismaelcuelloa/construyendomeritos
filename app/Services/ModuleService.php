<?php

namespace App\Services;

use App\Models\File;
use App\Models\Module;
use App\Models\ModuleFile;
use App\Validators\Courses\Modules\ModuleCopyValidator;
use App\Validators\Courses\Modules\ModulesValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ModuleService extends BaseService
{
    /**
     * @throws ValidationException
     */
    public function create(array $data): Module
    {
        \Log::info('Creando módulo con data:', $data);
        ModulesValidator::validate($data);

        try {
            $this->initTransactions();

            // Crear el módulo
            $module = new Module;
            $module->fill($data);
            $module->save();
            \Log::info('Módulo creado con ID: '.$module->id);

            // Procesar archivos PDF si existen
            if (isset($data['pdf_files']) && is_array($data['pdf_files'])) {
                foreach ($data['pdf_files'] as $index => $pdfFile) {
                    if ($pdfFile instanceof UploadedFile) {
                        $this->handlePdfFile($module, $pdfFile, $index + 1);
                    }
                }
            }

            // Recargar el módulo con sus archivos
            $module = Module::with(['files', 'files.file'])->find($module->id);

            $this->commitTransactions();
        } catch (\Exception $e) {
            $this->rollbackTransactions();
            throw $e;
        }

        return $module;
    }

    /**
     * Procesa y guarda el archivo PDF del módulo
     */
    private function handlePdfFile(Module $module, UploadedFile $pdfFile, int $fileNumber = 1): void
    {
        try {
            // Crear directorio específico para el módulo
            $modulePath = "files/modules/{$module->id}";
            $fullPath = public_path($modulePath);

            if (! file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            // Obtener el nombre original del archivo
            $originalFileName = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);

            // Generar nombre único para el archivo
            $fileName = Str::random(32).'.pdf';

            // Mover el archivo al directorio del módulo
            $pdfFile->move($fullPath, $fileName);

            // Crear registro en la tabla files
            $file = new File;
            $file->name = $fileName;
            $file->path = $modulePath;
            $file->type = File::TYPE_DOCUMENT; // Usar la constante correcta para documentos
            $file->save();

            // Crear la relación module_file con título dinámico
            $moduleFile = new ModuleFile;
            $moduleFile->module_id = $module->id;
            $moduleFile->file_id = $file->id;

            // Si es el primer archivo (material principal), usar el nombre original del PDF
            if ($fileNumber === 1) {
                $moduleFile->title = $originalFileName;
                $moduleFile->description = 'Material principal del módulo';
            } else {
                $moduleFile->title = $module->title." - Archivo $fileNumber";
                $moduleFile->description = "Archivo adicional $fileNumber del módulo";
            }

            $moduleFile->save();

        } catch (\Exception $e) {
            throw new \Exception('Error al procesar el archivo PDF: '.$e->getMessage());
        }
    }

    /**
     * @throws ValidationException
     */
    public function update(string|int $id, array $data): Module
    {
        $data['create'] = false;
        $data['id'] = $id;
        ModulesValidator::validate($data);

        try {
            $this->initTransactions();

            $module = Module::find($id);
            $module->fill($data);
            $module->save();

            // Procesar archivos PDF si existen
            if (isset($data['pdf_files']) && is_array($data['pdf_files'])) {
                foreach ($data['pdf_files'] as $index => $pdfFile) {
                    if ($pdfFile instanceof UploadedFile) {
                        $this->handlePdfFile($module, $pdfFile, $index + 1);
                    }
                }
            }

            // Recargar el módulo con sus archivos
            $module = Module::with(['files', 'files.file'])->find($module->id);

            $this->commitTransactions();
        } catch (\Exception $e) {
            $this->rollbackTransactions();
            throw $e;
        }

        return $module;
    }

    public function delete(string|int $id): void
    {
        try {
            $module = Module::query()->findOrFail($id);
        } catch (\Exception $e) {
            $this->error('Módulo no encontrado');
        }

        try {
            $this->initTransactions();

            $module->delete();

            $this->commitTransactions();
        } catch (\Exception $e) {
            $this->rollbackTransactions();
            throw $e;
        }

    }

    /**
     * @throws ValidationException
     */
    public function copy(array $data): Module
    {
        ModuleCopyValidator::validate($data);
        $id = $data['id'];
        $course_id = $data['course_id'];
        try {
            $this->initTransactions();

            $moduleOriginal = Module::find($id);
            $moduleCopy = $moduleOriginal->replicate();
            $moduleCopy->course_id = $course_id;
            $moduleCopy->save();

            foreach ($moduleOriginal->files as $fileOriginal) {
                $fileCopy = $fileOriginal->replicate();
                $fileCopy->module_id = $moduleCopy->id;
                $fileCopy->save();
            }

            $module = Module::with(['files'])->find($moduleCopy->id);

            $this->commitTransactions();

            return $module;
        } catch (\Exception $e) {
            $this->rollbackTransactions();
            throw $e;
        }

    }
}

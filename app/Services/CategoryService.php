<?php

namespace App\Services;

use App\Models\Category;
use App\Models\File;
use App\Validators\Categories\CategoriesValidator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryService extends BaseService
{
    /**
     * Creates a new category with the provided data.
     *
     * Validates the input data using the CategoriesValidator.
     * Generates a slug based on the category title and checks for duplicate slugs
     * in the database. If an image is provided in the data, an image record is created
     * and its ID is associated with the category. The category is then saved to the database.
     *
     * Transactions are used to ensure either all operations are successfully completed or
     * any errors encountered result in a rollback to maintain database integrity.
     *
     * @param  array  $data  The data used to create the category, including title and optional image.
     * @return Category The newly created category instance.
     *
     * @throws \Exception If an error occurs during the category creation process.
     */
    public function create(array $data): Category
    {

        CategoriesValidator::validate($data);

        try {
            $this->initTransactions();
            $data['slug'] = Str::slug($data['title']);

            $exist = Category::query()->where('slug', $data['slug'])->first();
            if ($exist) {
                $this->error('La categoría ya existe');
            }

            if (isset($data['image'])) {
                $data['image_id'] = $this->createImage($data['image'])->id;
            }

            $category = new Category;
            $category->fill($data);
            $category->save();

            $this->commitTransactions();

            return $category;
        } catch (\Exception $exception) {
            $this->rollbackTransactions();
            throw $exception;
        }
    }

    /**
     * @throws ValidationException
     */
    public function update(string|int $id, array $data): Category
    {
        $data['id'] = $id;
        $data['create'] = false;
        CategoriesValidator::validate($data);
        $this->clean($data, ['slug']);

        try {
            $this->initTransactions();

            if (isset($data['image'])) {
                $data['image_id'] = $this->createImage($data['image'])->id;
            }

            $category = Category::find($id);
            $category->fill($data);
            $category->save();

            $this->commitTransactions();

            return $category;
        } catch (\Exception $exception) {
            $this->rollbackTransactions();
            throw $exception;
        }
    }

    /**
     * @throws ValidationException
     */
    private function createImage($image): File
    {
        try {
            $file = [
                'file' => $image,
                'path' => 'files/categories',
                'type' => File::TYPE_IMAGE,
            ];

            return (new FileService)->setTransactions(false)->create($file);
        } catch (\Exception $e) {
            $this->error('Error al guardar la imagen');
        }
    }
}

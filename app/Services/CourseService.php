<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseCode;
use App\Models\CourseMetadata;
use App\Validators\Courses\CoursesValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CourseService extends BaseService
{
    /**
     * @throws ValidationException
     */
    public function create(array $data): Course
    {

        CoursesValidator::validate($data);

        try {
            $this->initTransactions();
            $data['slug'] = Str::slug($data['title']);

            $exist = Course::query()->where('slug', $data['slug'])->first();
            if ($exist) {
                $suffix = ! empty($data['code']) ? '-' . Str::slug($data['code']) : '-' . Str::random(6);
                $data['slug'] = Str::slug($data['title']) . $suffix;
            }

            $course = new Course;
            $course->fill($data);
            $course->save();

            if (! empty($data['codes']) && is_array($data['codes'])) {
                $this->syncCodes($course, $data['codes']);
            }

            $this->commitTransactions();

            return $course;
        } catch (\Exception $exception) {
            $this->rollbackTransactions();
            throw $exception;
        }
    }

    /**
     * @throws ValidationException
     */
    public function update(string|int $id, array $data): Course
    {
        $data['id'] = $id;
        $data['create'] = false;
        CoursesValidator::validate($data);
        $this->clean($data, ['slug']);

        try {
            $this->initTransactions();

            $course = Course::find($id);

            if (isset($data['title']) && $course->title !== $data['title']) {
                $baseSlug = Str::slug($data['title']);
                $slug = $baseSlug;
                $counter = 1;
                while (Course::where('slug', $slug)->where('id', '!=', $course->id)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }
                $data['slug'] = $slug;
            }

            $course->fill($data);
            $course->save();

            if (array_key_exists('codes', $data)) {
                $this->syncCodes($course, $data['codes'] ?? []);
            }

            $this->commitTransactions();

            return $course;
        } catch (\Exception $exception) {
            $this->rollbackTransactions();
            throw $exception;
        }

    }

    /**
     * @throws ValidationException
     */
    public function saveMetadata(string|int $id, array $data): CourseMetadata
    {
        $metadata = CourseMetadata::query()->course($id)->first();
        $this->clean($data, ['course_id']);

        if (isset($data['file'])) {
            try {
                $data['banner'] = $this->createFile($data['file'], $id);
            } catch (\Exception $e) {
                $this->error('Error al crear el archivo');
            }
        }

        if ($metadata == null) {
            $metadata = new CourseMetadata;
            $metadata->course_id = $id;
        }

        $metadata->fill($data);
        $metadata->save();

        return $metadata;
    }

    protected function syncCodes(Course $course, array $codes): void
    {
        $course->codes()->delete();

        $codes = array_filter(array_map('trim', $codes), fn ($c) => $c !== '');

        foreach ($codes as $code) {
            CourseCode::create([
                'course_id' => $course->id,
                'code' => $code,
            ]);
        }
    }

    public function createFile(UploadedFile $file, $id): ?string
    {

        try {
            $extension = $file->getClientOriginalExtension();
            $name = $id.'.'.$extension;
            $path = 'files/courses/';
            $file_name = $path.$name;
            $file->move(public_path($path), $name);

            return $file_name;
        } catch (\Exception $e) {
            throw $e;
        }

    }
}

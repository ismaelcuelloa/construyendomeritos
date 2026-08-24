<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BaseService
{
    protected bool $transactions = true;

    public function setTransactions(bool $transactions): static
    {
        $this->transactions = $transactions;

        return $this;
    }

    protected function initTransactions(): void
    {
        if ($this->transactions) {
            DB::beginTransaction();
        }
    }

    protected function commitTransactions(): void
    {
        if ($this->transactions) {
            DB::commit();
        }
    }

    protected function rollbackTransactions(): void
    {
        if ($this->transactions) {
            DB::rollBack();
        }
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    protected function error(string|array $message)
    {
        if (is_array($message)) {
            throw ValidationException::withMessages($message);
        } else {
            throw new \Exception($message);
        }
    }

    protected function clean(array &$data, array $columns, $timestamps = true): void
    {
        if ($timestamps) {
            unset($data['created_at']);
        }unset($data['updated_at']);

        foreach ($columns as $column) {
            unset($data[$column]);
        }
    }
}

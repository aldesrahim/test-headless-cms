<?php

namespace App\Rules;

use App\Models\Attachment;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;

class ReusableAttachment implements DataAwareRule, ValidationRule
{
    public array $fileRules = [];

    public array $data = [];

    public function __construct(
        array $fileRules = [],
    ) {
        $this->fileRules = $this->normalizeFileRules($fileRules);
    }

    public function normalizeFileRules(array $fileRules): array
    {
        if (blank($fileRules)) {
            return [];
        }

        $rules = [];

        foreach ($fileRules as $rule => $param) {
            if (is_numeric($rule)) {
                $rule = $param;
            } else {
                $param = is_array($param) ? implode(',', $param) : $param;
                $rule .= ":$param";
            }

            $rules[] = $rule;
        }

        return $rules;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if ($value instanceof UploadedFile) {
            if (blank($this->fileRules)) {
                return;
            }

            $validator = Validator::make($this->data, [
                $attribute => $this->fileRules,
            ]);

            if (! $validator->fails()) {
                return;
            }

            $fail($validator->errors()->first());

            return;
        }

        if (! Attachment::whereKey($value)->exists()) {
            $fail("Attachment with ID [$value] could not be found");
        }
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}

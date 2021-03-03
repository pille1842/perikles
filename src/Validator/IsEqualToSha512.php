<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsEqualToSha512 extends Constraint
{
    public $hash;

    public $message = 'The string "{{ string }}" is invalid.';
}

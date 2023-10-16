<?php

namespace App\Formatter;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationList;

class ValidationResultFormatter
{
    public function formatResult(ConstraintViolationList $constraintViolationList): array
    {
        $formattedResult = [];
        if($constraintViolationList->count()){
            /** @var Constraint $constraintViolation */
            foreach ($constraintViolationList as $constraintViolation){
                $formattedResult[$constraintViolation->getPropertyPath()][] = $constraintViolation->getMessage();
            }
        }
        return $formattedResult;
    }
}
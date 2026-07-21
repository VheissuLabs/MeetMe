<?php

namespace App\Services;

interface QuestionGenerator
{
    /** @return list<string> */
    public function generate(int $count): array;
}

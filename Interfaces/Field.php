<?php

interface Field
{
    public function getName();
    public function getValue();
    public function getDisplayValue();
    public function hasValue();
    public function getType();
    public static function Type();
}
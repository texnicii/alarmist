<?php

namespace Alarmist\Checkers;

interface CheckerInterface
{
	public function run(): \Generator;
	public function getName(): string;
}

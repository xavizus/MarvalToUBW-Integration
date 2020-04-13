<?php

/**
 * Creating a Config Class.
 * @var stdClass
 */
$config = new \stdClass;

/**
 * NVOA Kostnadsst�llen
 * @var array
 */

$config->NVOA = array(
    12345,
    12346,
);

$config->db = array(
    "server" => "localhost,1234",
    "database" => "MyTestDb",
    "username" => "user",
    "password" => "pass"
);

$config->specialCases = array(
    "24*",
);
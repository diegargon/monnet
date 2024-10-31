#!/bin/sh

pwd
ls -al
ls -al ./vendor/bin/phpunit
exec ./vendor/bin/phpunit

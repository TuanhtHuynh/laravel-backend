#!/bin/bash
php artisan migrate:fresh --seed --force
apache2-foreground
bash:
    - cd .. && docker-compose exec escola_lms_app bash -c "cd ../package"
test:
	- cd .. && docker-compose exec escola_lms_app bash -c "cd ../package && phpunit"
test-log:
	- cd .. && docker-compose exec escola_lms_app bash -c "cd ../package && cat ./vendor/orchestra/testbench-core/laravel/storage/logs/laravel.log"

log:
	- cd .. && cat host/storage/logs/laravel.log

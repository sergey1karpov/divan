1. git clone https://github.com/sergey1karpov/divan
2. docker compose up -d
3. composer update
4. docker compose exec app bash
5. php artisan migrate

Api документация http://localhost/api/documentation/

Тестирование запускается из контейнера
1. docker compose exec app bash
2. php artisan test

copy file .env.example menjadi .env

buat database di mysql: db_hariwicaksono_tes_online_ptsms
pastikan sudah ada node.js dan composer

buka terminal lalu jalankan 
composer update atau composer install
lalu
npm install

jalankan perintah
php artisan migrate
atau import file sql: db_hariwicaksono_tes_online_ptsms.sql

jalankan perintah
composer run dev

atau 

jalankan dengan buka dua terminal
terminal 1
php artisan serve

lalu terminal 2
npm run dev

buka browser
http://localhost:8000

jika menggunakan laragon buka dengan http://hariwicaksono_tes_online_ptsms.test

login menggunakan 
admin@test.com
12345678
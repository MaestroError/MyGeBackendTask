## Installation
- `git clone https://github.com/MaestroError/MyGeBackendTask.git`
- `composer install`
- `cp .env.example .env`
- `php artisan key:generate`
- `php artisan migrate:fresh --seed`

### Testing
All tests are done well, you can easly run `php artisan test`     
Or `php artisan serve` and test endpoints via postman, creds:   
- name: Task user for cart
- email: cart@user.com
- password: 12345678

#### Some needed enpoints
- /user/login: POST - email, password
- /user/register: POST - name, email, password, password_confirmation
- /user/logout: POST - Needs bearer auth token    

P.s. all routes demanded in task needs authentification, get bearer token from user/login or user/register endpoint
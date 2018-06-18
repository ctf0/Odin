# Odin

[![Latest Stable Version](https://img.shields.io/packagist/v/ctf0/odin.svg)](https://packagist.org/packages/ctf0/odin) [![Total Downloads](https://img.shields.io/packagist/dt/ctf0/odin.svg)](https://packagist.org/packages/ctf0/odin) [![Donate with Bitcoin](https://en.cryptobadges.io/badge/micro/16ri7Hh848bw7vxbEevKHFuHXLmsV8Vc9L)](https://en.cryptobadges.io/donate/16ri7Hh848bw7vxbEevKHFuHXLmsV8Vc9L)

Manage model revisions with ease.

<details><summary>Preview</summary>

![preview](https://user-images.githubusercontent.com/7388088/33775349-be6f1696-dc46-11e7-880f-693a47d86b52.jpg)
</details>

- package requires Laravel v5.4+

<br>

## Installation

- `composer require ctf0/odin`

- (Laravel < 5.5) add the service provider & facade

    ```php
    'providers' => [
        ctf0\Odin\OdinServiceProvider::class,
    ];
    ```

* publish the package assets with

    `php artisan vendor:publish --provider="ctf0\Odin\OdinServiceProvider"`

- after installation, package will auto-add
    + package routes to `routes/web.php`
    + package assets compiling to `webpack.mix.js`

- check http://www.laravel-auditing.com/docs/7.0/general-configuration for configuration

- install dependencies

    ```bash
    yarn add vue vue-awesome@v2 vue-notif axios keycode
    # or
    npm install vue vue-awesome@v2 vue-notif axios keycode --save
    ```

- add this one liner to your main js file and run `npm run watch` to compile your `js/css` files.
    + if you are having issues [Check](https://ctf0.wordpress.com/2017/09/12/laravel-mix-es6/).

    ```js
    require('../vendor/Odin/js/manager')

    new Vue({
        el: '#app'
    })
    ```

<br>

## Features

- support single & nested values.
- delete & restore revisions.
- don't save the audit record if the model `old_value & new_value` are empty.
- support soft deletes.
- [revision preview](https://github.com/ctf0/Odin/wiki/Preview-Revision).
- clear audits for permanently deleted models.
    ```bash
    php artisan odin:gc
    ```

    which can be scheduled as well
    ```php
    $schedule->command('odin:gc')->sundays();
    ```

- shortcuts

    |      navigation      |  keyboard  |    mouse (click)    |
    |----------------------|------------|---------------------|
    | go to next revision  | right/down | * *(revision date)* |
    | go to prev revision  | left/up    | * *(revision date)* |
    | go to first revision | home       | * *(revision date)* |
    | go to last revision  | end        | * *(revision date)* |
    | hide revision window | esc        | * *(x)*             |

- events "[JS](https://github.com/gocanto/vuemit)"

    | event-name |       description       |
    |------------|-------------------------|
    | odin-show   | when revision is showen |
    | odin-hide   | when revision is hidden |

<br>

## Usage

- run `php artisan migrate`

- add `Revisions` trait & `AuditableContract` contract to your model
    + for `User model` [Check](http://laravel-auditing.com/docs/7.0/audit-resolvers)

    ```php

    use ctf0\Odin\Traits\Revisions;
    use Illuminate\Database\Eloquent\Model;
    use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

    class Post extends Model implements AuditableContract
    {
        use Revisions;

        // ...
    }
    ```

- inside the model view ex.`post edit view` add

    ```blade
    @if (count($post->$menu->revisionsWithRelation))
        @include('Odin::list', ['revisions' => $post->$menu->revisionsWithRelation])
    @endif
    ```

<br>

## Notes For `data:uri`

- if you use `data:uri` in your revisionable content, change [`audits_table`](https://github.com/owen-it/laravel-auditing/blob/958a6edd4cd4f9d61aa34f288f708644e150e866/database/migrations/audits.stub#L33-L34) columns type to either `mediumText` or `longText` before migrating to avoid future errors of long data.

- because `data:uri` is render blocking & isn't readable by humans, we truncate it to 75 char max **(the smallest stable data:uri is 78 char)**,

    note that this ***ONLY*** effects the displaying of the revision diff, we never touch the data that gets saved to the db.

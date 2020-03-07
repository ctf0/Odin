<h1 align="center">
    Odin
    <br>
    <a href="https://packagist.org/packages/ctf0/odin"><img src="https://img.shields.io/packagist/v/ctf0/odin.svg" alt="Latest Stable Version" /></a> <a href="https://packagist.org/packages/ctf0/odin"><img src="https://img.shields.io/packagist/dt/ctf0/odin.svg" alt="Total Downloads" /></a>
</h1>

Manage model revisions with ease.
> If you are also looking to preview the form data before submitting to the db, you may want to give [OverSeer](https://github.com/ctf0/OverSeer) a try.

<p align="center">
    <a href="https://user-images.githubusercontent.com/7388088/33775349-be6f1696-dc46-11e7-880f-693a47d86b52.jpg"><img src="https://user-images.githubusercontent.com/7388088/33775349-be6f1696-dc46-11e7-880f-693a47d86b52.jpg"></a>
</p>

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

- after installation, run `php artisan odin:setup` to add
    + package routes to `routes/web.php`
    + package assets compiling to `webpack.mix.js`

- check [laravel-auditing docs](http://www.laravel-auditing.com/docs/master/general-configuration) for configuration

- install dependencies

    ```bash
    yarn add vue vue-awesome@v2 vue-notif axios keycode
    ```

- add this one liner to your main js file and run `npm run watch` to compile your `js/css` files.
    + if you are having issues [Check](https://ctf0.wordpress.com/2017/09/12/laravel-mix-es6/).

    ```js
    // app.js

    window.Vue = require('vue')

    require('../vendor/Odin/js/manager')

    new Vue({
        el: '#app'
    })
    ```

<br>

## Features

- support single & nested values.
- delete & restore revisions.
- support soft deletes.
- [revision preview](https://github.com/ctf0/Odin/wiki/Preview-Revision).
- clear audits for permanently deleted models.

    ```bash
    php artisan odin:gc
    ```

    + which can be scheduled as well
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
    + for `User model` [Check](http://laravel-auditing.com/docs/master/audit-resolvers)

    ```php

    use ctf0\Odin\Traits\Revisions;
    use Illuminate\Database\Eloquent\Model;
    use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

    class Post extends Model implements AuditableContract
    {
        use Revisions;

        /**
         * resolve model title/name for the revision relation
         * this is needed so we can render
         * the model relation attach/detach changes
         */
        public function getMiscTitleAttribute()
        {
            return $this->name;
        }

        // ...
    }
    ```

- you can disable creating **ghost** audits where both `old/new` values are empty by using
    + remember that without the parent model audit log we cant show the relation changes

    ```php
    // app/Providers/EventServiceProvider

    use OwenIt\Auditing\Models\Audit;

    public function boot()
    {
        parent::boot();

        Audit::creating(function (Audit $model) {
            if (empty($model->old_values) && empty($model->new_values)) {
                return false;
            }
        });
    }
    ```

- inside the model view ex.`post edit view` add

    ```blade
    @if (count($post->revisionsWithRelation))
        @include('Odin::list', ['revisions' => $post->revisionsWithRelation])
    @endif
    ```

<br>

## Notes

- model `user_id` & `id` are excluded from the audit log by default.

- **data:uri**
    - if you use `data:uri` in your revisionable content, change [`audits_table`](https://github.com/owen-it/laravel-auditing/blob/958a6edd4cd4f9d61aa34f288f708644e150e866/database/migrations/audits.stub#L33-L34) columns type to either `mediumText` or `longText` before migrating to avoid future errors of long data.

    - because `data:uri` is a render blocking & isn't readable by humans, we truncate it to 75 char max<br>
        note that this ***ONLY*** effects the displaying of the revision diff, we never touch the data that gets saved to the db.

- **model-relation**
    + atm the relation revision is limited, it means we can only show the `attach/detach` changes but we cant `undo/redo` any of them through the package it self.
    + also if you use mass update like `Model::update()` make sure to call `$model->touch();` afterwards to make sure an audit is created ex.
        ```php
        $model = Model::update([...]);
        $model->touch();
        ```

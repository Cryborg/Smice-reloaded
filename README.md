<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<table>
    <tr>
        <td><b style="font-size:0.8em">Laravel</b> 9</td>
        <td><b style="font-size:0.8em">PHP</b> 8.1</td>
    </tr>
</table>

# Smice {Reloaded}

## Introduction

Smice Reloaded is the next-gen Smice+ platform.

## Installation

Create a bash alias for <a href="https://laravel.com/docs/9.x/sail">Laravel Sail</a>.
```
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
```

Run the application 
```
// Run and configure Docker images
$ sail up

// Configure the application
$ sail composer install
$ sail artisan key:generate
$ sail artisan migrate --seed
```

Go to <a href="http://0.0.0.0:8080">`http://0.0.0.0:8080`</a> to confirm the application is up and running.

## Laravel Sail

We use <a href="https://laravel.com/docs/9.x/sail">Laravel Sail</a> to ease Docker management.

When you need to run a command, do it using Sail:
```
$ sail artisan route:list
$ sail composer install
$ sail php -v
```

## API documentation

The technical documentation can be automatically generated.
```
sail artisan lrd:g
```

To access it, go to <a href="http://0.0.0.0:8080/request-docs"> `http://0.0.0.0:8080/request-docs` </a>

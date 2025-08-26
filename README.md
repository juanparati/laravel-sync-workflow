# Laravel synchronous workflows

## What is it?

A library that runs synchronous replicable workflows with event sourcing support in Laravel.

The workflows are executed in batch by the same process and are not distributed.

For distributed asynchronous workflows, see [Laravel Workflow](https://github.com/laravel-workflow/laravel-workflow).

It supports the following features:
- Synchronous workflows
- Event sourcing
- Workflow history
- Workflow replay


## Installation

```sh
composer require juanparati/laravel-sync-workflow
```

Publish migrations and config file (Required for event sourcing):

```sh
artisan vendor:publish --tag=laravel-sync-workflow
```

Run migrations:

```sh
artisan migrate
```

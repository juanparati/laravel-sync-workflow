# Laravel Synchronous Workflows

A robust library for executing reproducible synchronous workflows with seamless event sourcing capabilities in Laravel.

Workflow activities are executed sequentially within a single process and are not distributed across multiple instances or jobs.

Each time that an activity is executed, its input is passed to the next activity in the workflow, and objects are decoupled from their original reference to avoid non-desired mutability.

For distributed asynchronous workflows, see [Laravel Workflow](https://github.com/laravel-workflow/laravel-workflow).

Key features include:
- Synchronous workflow execution
- Event sourcing capabilities
- Comprehensive workflow history tracking
- Workflow replay functionality
- Automatic object reference decoupling
- Relative time management

This library is inspired by [Laravel Workflow](https://github.com/laravel-workflow/laravel-workflow) and [Laravel Saga](https://github.com/laravel-sagas/laravel-sagas).

## Installation

```sh
composer require juanparati/laravel-sync-workflow
```

Publish migrations and configuration files (required for event sourcing):

```sh
artisan vendor:publish --tag=laravel-sync-workflow
```

Run migrations:

```sh
artisan migrate
```


## Usage


### Basic Workflow Example

Here's a simple workflow that processes user registration:

```php
<?php

namespace App\SyncWorkflows;

use App\SyncWorkflows\UserRegistration\SendWelcomeEmail;
use App\SyncWorkflows\UserRegistration\CreateUserProfile;
use Juanparati\SyncWorkflows\SyncWorkflow;

class UserRegistrationWorkflow extends SyncWorkflow
{

    protected User $user;

    public function __construct(User|array $user)
    {
        $this->user = $user instanceof User ? $user : new User($user);
    } 
    
    
    public function handle()
    {
        // Create user profile
        $profile = $this->executor()->runActivity(
            CreateUserProfile::class,
            $this->user
        );

        // Send welcome email
        $this->executor()->runActivity(
            SendWelcomeEmail::class,
            $this->user
        );

        return ['user_id' => $profile->id, 'status' => 'registered'];
    }
}
```

or as a chain of activities:

```php
<?php

namespace App\SyncWorkflows;

use App\SyncWorkflows\UserRegistration\SendWelcomeEmail;
use App\SyncWorkflows\UserRegistration\CreateUserProfile;
use Juanparati\SyncWorkflows\Contracts\WithEventSourcing;
use Juanparati\SyncWorkflows\SyncWorkflow;

// When implementing WithEventSourcing, the workflow will be persisted in the database
// and its execution history will be available for replay.
class UserRegistrationWorkflow extends SyncWorkflow implements WithEventSourcing
{

    protected User $user;

    public function __construct(User|array $user)
    {
        $this->user = $user instanceof User ? $user : new User($user);
    } 
       
    public function handle()
    {
        $profile = $this->executor()->runChainedActivities([
            CreateUserProfile::class,
            SendWelcomeEmail::class,
        ], $this->user);    // The output of one activity is the input of the next
              
        return ['user_id' => $profile->id, 'status' => 'registered'];
    }
}
```

### Activity Example

Activities contain the actual business logic:

```php
<?php

namespace App\SyncWorkflows\UserRegistration;

use App\Models\User;
use Juanparati\SyncWorkflows\SyncActivity;

class CreateUserProfile extends SyncActivity
{

    public function __construct(protected User $user);
    
    public function execute()
    {   
        // Use relativeNow instead of now() to ensure consistent timestamps during workflow replay
        // by preserving the original execution time.
        $this->user->created_at = $this->executor()->relativeNow();
        $this->user->email_verified = true;
        $this->user->save();
            
        return $this->user;
    }
}
```

### Running Workflows

Execute a workflow programmatically:

```php
use Juanparati\SyncWorkflows\SyncExecutor;

$result = SyncExecutor::dispatch(
    new UserRegistrationWorkflow(['email' => 'user@example.com', 'name' => 'John Doe'])
);

// Access the result
echo "User registered with ID: " . $result->id;
```

or alternatively:

```php
use Juanparati\SyncWorkflows\SyncExecutor;

$workflow = SyncExecutor::make()
    ->load(new UserRegistrationWorkflow(['email' => 'user@example.com', 'name' => 'John Doe']));
    
echo "Workflow ID: " . $workflow->getId();

$workflow->run();

echo "Workflow finished at " . $workflow->getExecutionTime()['endedAt'];

$result = $workflow->getResult();

// Access the result
echo "User registered with ID: " . $result->id;
```

### Controlled Exceptions

To gracefully halt workflow execution, you can throw a `SyncWorkflowControlledException` from within an activity:

```php
<?php

namespace App\SyncWorkflows\OrderProcessing;

use Juanparati\SyncWorkflows\Exceptions\SyncWorkflowControlledException;
use Juanparati\SyncWorkflows\SyncActivity;
use App\Services\PaymentService;
use Exception;

class ValidatePayment extends SyncActivity
{
    public function execute()
    {   
        $paymentPermission = PaymentService::obtainPermission($this->input);
        
        if (!$paymentPermission) {
            throw (new SyncWorkflowControlledException('Permission denied'))
                ->addError(['info' => $this->input]);
        }
        
        return $paymentPermission;                
    }
}
```

You can handle the exception in your workflow:

```php
try {
    SyncExecutor::dispatch(new OrderProcessingWorkflow($order));
} catch (SyncWorkflowControlledException $e) {
    \Log::warning('Order process cancelled: ' . $e->getMessage(), $e->getErrors());
} catch (Exception $e) {
    \Log::error('Unable to process order: ' . $e->getMessage());   
    throw $e;
}
```


### Commands

#### Generate a new workflow

```sh
artisan make:sync-workflow MyWorkflow
```

The workflow will be created in the `app/SyncWorkflows` directory.

#### Generate a new activity

```sh
artisan make:sync-workflow-activity MyWorkflow/MyFirstActivity
```

#### Replay a workflow

```sh
artisan sync-workflow:replay [workflow-id]
```

#### View workflow state

```sh
artisan sync-workflow:view [workflow-id]
```



